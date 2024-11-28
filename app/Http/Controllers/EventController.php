<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;

class EventController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    
    public function __construct(){
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->collection = $this->firestore->database()->collection('events');
        $this->bucket = $this->storage->getBucket();
    }

    public function index(){
        $documents = $this->collection->documents();
        $events = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $event = $document->data();
                $event['id'] = $document->id();
                $events[] = $event;
            }
        }
        // dd($events);

        return view('Events.event', compact('events'));
    }
}
