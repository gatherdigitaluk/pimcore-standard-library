<?php

/**
 * Email
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore\Tool;

use Pimcore\Mail as PimcoreMail;

class Email
{

    /**
     * Sends an HTML Email using the \Pimcore\Mail
     * @param        $htmlContent
     * @param        $to
     * @param string $subject
     * @param string $replyTo
     * @return bool
     */
    public static function sendHtml($htmlContent, $to, $subject = '', $replyTo = '')
    {
        $mail = new PimcoreMail();
        $mail->setSubject($subject);
        $mail->addTo($to);
        $mail->setBodyHtml($htmlContent);
        $mail->setReplyTo($replyTo);

        return $mail->send();
    }


    /**
     * Sends an HTML Email with an Attachement, using \Pimcore\Mail
     * @param        $htmlContent
     * @param        $to
     * @param        $subject
     * @param        $attachmentFilepath
     * @param string $replyTo
     * @return bool
     */
    public static function sendHtmlWithAttachment($htmlContent, $to, $subject, $attachmentFilepath, $replyTo = '')
    {
        if (!file_exists($attachmentFilepath)) {
            \Logger::notice('Attempting to send an email attachment, but file did not exist');
            return self::sendHtml($htmlContent, $to, $subject);
        }

        $mail = new PimcoreMail();
        $mail->setSubject($subject);
        $mail->addTo($to);
        $mail->setBodyHtml($htmlContent);
        $mail->setReplyTo($replyTo);

        //generate a pdf for the sale
        $attachment = new \Zend_Mime_Part(file_get_contents($attachmentFilepath));
        $attachment->type = 'application/pdf';
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = basename($attachmentFilepath); // name of file

        $mail->addAttachment($attachment);

        return $mail->send();
    }
}
