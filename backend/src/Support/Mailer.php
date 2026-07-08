<?php

declare(strict_types=1);

namespace App\Support;

use App\Repository\PrivateSettingRepository;
use App\Repository\SettingRepository;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Odesílání e-mailů přes SMTP nastavené v adminu (tabulka private_settings).
 * Tenký wrapper nad PHPMailer; jediné místo, kudy backend posílá poštu.
 */
final class Mailer
{
    /** @var array<string, string> */
    private array $config;

    public function __construct(?PrivateSettingRepository $settings = null)
    {
        $this->config = ($settings ?? new PrivateSettingRepository())->all();
    }

    /** Je SMTP nastavené natolik, aby šlo vůbec odeslat? */
    public function isConfigured(): bool
    {
        return trim($this->config['smtp_host'] ?? '') !== ''
            && trim($this->config['smtp_from_email'] ?? '') !== '';
    }

    /**
     * Odešle e-mail přes nastavené SMTP.
     *
     * @throws RuntimeException s čitelnou zprávou, když odeslání selže
     */
    public function send(string $to, string $subject, string $body): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('SMTP není nastavené (chybí server nebo adresa odesílatele).');
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Host = $this->config['smtp_host'] ?? '';
            $mail->Port = (int) ($this->config['smtp_port'] ?? 587);

            $encryption = $this->config['smtp_encryption'] ?? 'tls';
            $mail->SMTPSecure = match ($encryption) {
                'ssl' => PHPMailer::ENCRYPTION_SMTPS,
                'tls' => PHPMailer::ENCRYPTION_STARTTLS,
                default => '',
            };
            if ($encryption !== 'tls' && $encryption !== 'ssl') {
                $mail->SMTPAutoTLS = false;
            }

            $username = $this->config['smtp_username'] ?? '';
            if ($username !== '') {
                $mail->SMTPAuth = true;
                $mail->Username = $username;
                $mail->Password = $this->config['smtp_password'] ?? '';
            }

            $mail->setFrom(
                $this->config['smtp_from_email'] ?? '',
                $this->config['smtp_from_name'] ?? ''
            );
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
        } catch (PHPMailerException $exception) {
            // ErrorInfo bývá čitelnější popis SMTP chyby než zpráva výjimky.
            $detail = $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $exception->getMessage();

            throw new RuntimeException($detail, 0, $exception);
        }
    }

    /** Pošle testovací e-mail na zadanou adresu (pro ověření nastavení SMTP). */
    public function sendTest(string $to): void
    {
        $siteTitle = (new SettingRepository())->get('site_title', 'web') ?: 'web';
        $this->send(
            $to,
            'Test SMTP z administrace (' . $siteTitle . ')',
            "Toto je testovací e-mail z administrace webu {$siteTitle}.\n\n"
            . 'Pokud vám dorazil, odesílání e-mailů přes SMTP funguje.'
        );
    }
}
