<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $this->firestore = app('firebase.firestore');
    }

    public function index()
    {
        // Hitung jumlah mahasiswa dari koleksi Firestore
        $collection = $this->firestore->database()->collection('users');
        $documents = $collection->where('role', '=', 'user')->documents();
        $mahasiswaCount = 0;

        foreach ($documents as $document) {
            if ($document->exists()) {
                $mahasiswaCount++;
            }
        }

        $collectionEvent = $this->firestore->database()->collection('events');
        $documentsEvent = $collectionEvent->documents();
        $EventCount = 0;

        foreach ($documentsEvent as $document) {
            if ($document->exists()) {
                $EventCount++;
            }
        }

        $collectionBanner = $this->firestore->database()->collection('banners');
        $documentsBanner = $collectionBanner->documents();
        $bannerCount = 0;

        foreach ($documentsBanner as $document) {
            if ($document->exists()) {
                $bannerCount++;
            }
        }

        $collectionPengelola = $this->firestore->database()->collection('users');
        $documentsPengelola = $collection->where('role', '=', 'pengelola')->documents();
        $PengelolaCount = 0;

        foreach ($documentsPengelola as $document) {
            if ($document->exists()) {
                $PengelolaCount++;
            }
        }

        // Kirim data ke view
        return view('dashboard', compact('mahasiswaCount','EventCount','bannerCount','PengelolaCount'));
    }
}

