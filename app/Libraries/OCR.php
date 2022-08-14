<?php
namespace App\Libraries;

use Google\Cloud\Vision\VisionClient;

class OCR {
    private $vision;

    public function getText($path){

        $text = '';

        try {
            $vision = new VisionClient([
                'keyFilePath' => env('GOOGLE_APPLICATION_CREDENTIALS')
            ]);

            $imageResource = fopen($path, 'r');
            $image = $vision->image($imageResource, [ 'DOCUMENT_TEXT_DETECTION' ]);
            $annotation = $vision->annotate($image);

            $fullText = $annotation->fullText();

            return $fullText ? $fullText->text() : null;

        } catch(Exception $e){
            return $text;
        }
    }
}