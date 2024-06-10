<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\FontType;

class FontTypeService {
    function __construct() {
    }

    /*
        Scans the fonts directory for a list of available font types. It also returns 
        a list of enabled languages for them.
    */
    public function getInstalledFontTypes() {
        $fonts = FontType::get();
        return $fonts;
    }

    public function getFontTypesForLanguage($language) {
        $fonts = FontType
            ::orderBy('default', 'desc')
            ->get();

        $fonts = $fonts->filter(function ($font) use($language) {
            $fontLanguages = json_decode($font->languages);
            return in_array($language, $fontLanguages, true);
        });

        return array_values($fonts->toArray());
    }

    public function uploadFontType($fontFile, $fontName, $fontLanguages) {
        /*
            File names that start with Default must be renamed. 
            Those file names are reserved for default font names.
        */
        $fileName = str_replace('Default', 'NotDefault', $fontFile->getClientOriginalName());

        if (Storage::exists('fonts/' . $fileName)) {
            throw new \Exception('The font file already exists on the server.');
        }

        // upload file
        $fontFile->move(storage_path('app/fonts'), $fileName);

        // create font in database
        $fontType = new FontType();
        $fontType->filename = $fileName;
        $fontType->name = $fontName;
        $fontType->languages = $fontLanguages;
        $fontType->default = false;
        $fontType->save();
        
        return true;
    }

    public function updateFontType($fontId, $fontName, $fontLanguages) {
        $fontType = FontType
            ::where('default', false)
            ->where('id', $fontId)
            ->first();

        if (!$fontType) {
            throw new \Exception('Font not found.');
        }

        $fontType->name = $fontName;
        $fontType->languages = $fontLanguages;
        $fontType->save();

        return true;
    }

    public function deleteFontType($fontId) {
        $fontType = FontType
            ::where('default', false)
            ->where('id', $fontId)
            ->first();

        if (!$fontType) {
            throw new \Exception('Font not found.');
        }

        // delete file
        Storage::delete('fonts/' . $fontType->filename);

        // delete database record
        $fontType->delete();

        return true;
    }
}