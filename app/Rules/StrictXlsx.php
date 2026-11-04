<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use ZipArchive;

class StrictXlsx implements Rule
{
    public function passes($attribute, $value)
    {
        if (!$value->isValid()) {
            return false;
        }

        $path = $value->getRealPath();

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $path);
        finfo_close($finfo);

        if ($mime !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            return false;
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== TRUE) {
            return false;
        }

        // Apparently it's a file structure so...
        $required = ['[Content_Types].xml', '_rels/.rels', 'xl/styles.xml', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels'];
        foreach ($required as $file) {
            if ($zip->locateName($file) === false) {
                $zip->close();
                return false;
            }
        }

        // Amateur zip bombs go bye-bye
        $totalUncompressed = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $totalUncompressed += $stat['size'];

            if ($totalUncompressed > 10 * 1024 * 1024) {
                $zip->close();
                return false;
            }
        }

        $doc = $zip->getFromName('xl/workbook.xml');
        if ($doc === false) {
            $zip->close();
            return false;
        }

        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($doc);
        $zip->close();

        if ($xml === false) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return '';
    }
}