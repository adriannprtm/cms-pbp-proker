<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;
use Kreait\Firebase\Database;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Google\Cloud\Core\Timestamp;
use Exception;

class PengelolaController extends Controller
{
    protected $firestore;
    protected $storage;
    protected $collection;
    protected $bucket;
    protected $auth;
    
    public function __construct(){
        $this->firestore = app('firebase.firestore');
        $this->storage = app('firebase.storage');
        $this->auth = app('firebase.auth');
        $this->collection = $this->firestore->database()->collection('users');
        $this->bucket = $this->storage->getBucket();
    }

    public function index(){
        $documents = $this->collection->documents();
        $pengelolas = [];

        foreach ($documents as $document) {
            if ($document->exists()) {
                $pengelola = $document->data();
                $pengelola['id'] = $document->id();
                if (isset($pengelola['role']) && $pengelola['role'] === 'pengelola') {
                    $pengelolas[] = $pengelola;
                }
            }
            // dd($mahasiswas);
        }

        return view('Pengelola.pengelola', compact('pengelolas'));
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
                'firstName' => 'required',
                'lastName' => 'nullable',
                'image' => 'image|mimes:jpeg,png,jpg|max:2048' // Validasi untuk gambar
            ]);
            // dd($request);
            // 1. Buat user di Firebase Authentication
            $userAuth = $this->auth->createUser([
                'email' => $request->email,
                'password' => $request->password,
                'emailVerified' => false,
            ]);
            // dd($userAuth);

            // 2. Upload gambar jika ada
            $imageUrl = '';
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $path = 'profile_images/' . $userAuth->uid . '/' . $imageName;
                
                $imageObject = $this->bucket->upload(
                    file_get_contents($image->getRealPath()),
                    ['name' => $path]
                );

                // Dapatkan URL gambar
                // $imageUrl = 'https://storage.googleapis.com/' . $this->bucket->name() . '/' . $path;

                $imageUrl = sprintf(
                    'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                    $this->bucket->info()['name'],
                    urlencode($path)
                );
            }

            $lastName = $request->lastName ?? '';

            $userData = [
                'createdAt' => new Timestamp(new \DateTime()),
                'email' => $request->email,
                'firstName' => $request->firstName,
                'imageUrl' => $imageUrl,
                'lastName' => $lastName,
                'lastSeen' => new Timestamp(new \DateTime()),
                'name' => $request->firstName . ' ' . $request->lastName,
                'role' => 'pengelola',
                'updatedAt' => new Timestamp(new \DateTime())
            ];
            // dd($userData);

            // 4. Simpan ke Firestore menggunakan UID dari Auth
            $this->collection->document($userAuth->uid)->set($userData);

            return redirect()->route('pengelola.index')->with('success', 'Mahasiswa berhasil ditambahkan');
        } catch (EmailExists $e) {
            return redirect()->back()->with('error', 'Email sudah terdaftar');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
