<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

// services
use App\Services\LanguageService;
use App\Services\GoalService;

// request classes
use App\Http\Requests\Languages\InstallLanguageRequest;
use App\Http\Requests\Languages\ChangeLanguageRequest;

class LanguageController extends Controller {
    private $languageService;
    private $goalService;

    function __construct(LanguageService $languageService, GoalService $goalService) {
        $this->languageService = $languageService;
        $this->goalService = $goalService;
    }

    public function getLanguageSelectionDialogData() {
        $supportedSourceLanguages = config('linguacafe.languages.supported_languages');
        $installableLanguages = config('linguacafe.languages.supported_languages_with_required_install');

        try {
            $languageData = $this->languageService->getLanguageSelectionDialogData($supportedSourceLanguages, $installableLanguages);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($languageData, 200);
    }

    public function getAdminLanguageSettingsData() {
        $installableLanguages = config('linguacafe.languages.supported_languages_with_required_install');

        try {
            $installedLanguages = $this->languageService->getInstalledLanguages();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        $responseData = new \stdClass();
        $responseData->languages = $installableLanguages;
        $responseData->installedLanguages = $installedLanguages;

        return response()->json($responseData, 200);
    }

    /*
        This function will not change the language if it's not installed.
        Since this should never happen in the software, it does not
        throw an exception.
    */
    public function selectLanguage($language, ChangeLanguageRequest $request) {
        $user = Auth::user();
        
        try {
            $this->languageService->selectLanguage($user, $language);
            $this->goalService->createGoalsForLanguage($user->id, $language);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json('Language has been changed successfully.', 200);
    }

    public function getInstalledLanguages() {
        try {
            $installedLanguages = $this->languageService->getInstalledLanguages();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($installedLanguages, 200);
    }


    public function installLanguage(InstallLanguageRequest $request) {
        $installableLanguages = config('linguacafe.languages.supported_languages_with_required_install');
        $language = $request->post('language');

        try {
            $installResult = $this->languageService->installLanguage($language, $installableLanguages);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        
        if ($installResult->getStatusCode() !== 200) {
            return response()->json("An error has occured.", 500);
        }
        

        return response()->json('Language has been installed successfully.', 200);
    }

    public function deleteInstalledLanguages() {
        $installableLanguages = config('linguacafe.languages.supported_languages_with_required_install');
        $user = Auth::user();

        try {
            $uninstallResult = $this->languageService->deleteInstalledLanguages($user, $installableLanguages);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        if ($uninstallResult->getStatusCode() !== 200 && $uninstallResult->getStatusCode() !== 202) {
            return response()->json("An error has occured.", 500);
        }

        return response()->json('Installed languages has been deleted successfully.', 200);        
    }
}
