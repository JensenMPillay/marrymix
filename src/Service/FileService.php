<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileService extends AbstractController
{
    public function uploadImage(UploadedFile $imageFile, SluggerInterface $slugger, $directoryName)
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
        // Move the file to the directory where images are stored
        try {
            $imageFile->move(
                $this->getParameter($directoryName),
                $newFilename
            );
        } catch (FileException) {
            // ... handle exception if something happens during file upload
        }

        return $newFilename;
    }
}
