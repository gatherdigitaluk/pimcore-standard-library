<?php

/**
 * PdfGenerator
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md
 * file distributed with this source code.
 *
 * @copyright  Copyright (c) 2014-2016 Gather Digital Ltd (https://www.gatherdigital.co.uk)
 * @license    https://www.gatherdigital.co.uk/license     GNU General Public License version 3 (GPLv3)
 */

namespace Gdl\Pimcore\Tool;

use Pimcore\Tool\Console;

class PdfGenerator
{

    /**
     * Generates a PDF using wkhtmltopdf given an URL
     * @param        $url
     * @param        $saveFilename
     * @param string $config
     * @param string $saveFilepath
     * @return bool|string
     */
    public static function generateFromUrl($url, $saveFilename, $config=' --margin-left 0 --margin-right 0 ', $saveFilepath='/tmp')
    {
        $returnFilepath = $saveFilepath .'/' . $saveFilename;

        if (file_exists('/usr/local/bin/wkhtmltopdf.sh')) {
            $cmd = sprintf('/usr/local/bin/wkhtmltopdf.sh %s "%s" "%s"', $config, $url, $returnFilepath);
        } else if (file_exists('/usr/bin/wkhtmltopdf')) {
            $cmd = sprintf('/usr/bin/wkhtmltopdf %s "%s" "%s"', $config, $url, $returnFilepath);
        } else {
            $cmd = sprintf('wkhtmltopdf %s "%s" "%s"', $config, $url, $returnFilepath);
        }

        $result = Console::exec($cmd);

        //get the file
        if (file_exists($returnFilepath)) {
            \Pimcore\Log\Simple::log('errors_pdf-generator', 'Generated PDF: ' . $cmd);
            return $returnFilepath;
        } else {
            \Pimcore\Log\Simple::log('errors_pdf-generator', 'Could not generate PDF from ' . $url);
            \Pimcore\Log\Simple::log('errors_pdf-generator', 'CMD was' . $cmd);
            return false;
        }

    }


}
