<?php

namespace App\Controller\Admin;

use App\Service\UploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(): Response
    {
        return $this->render('admin/default/index.html.twig');
    }

    #[Route('/file-uploader', name: 'admin_file_uploader')]
    public function fileUploader(Request $request, UploadService $uploadService): Response
    {
        try {
            $file = $uploadService->saveUploadedFile(
                $request->files->get('file')
            );
            return new JsonResponse([
                'status' => true,
                'filename' => '/uploads/editor/' . $file->getFilename(),
            ]);
        } catch (\Exception $exception) {
            //..
        }

        return new JsonResponse([
            'status' => false,
        ]);
    }
}
