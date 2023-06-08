<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Dompdf\Options;
use Twig\Environment;

class FileService extends AbstractController

{
    private Environment $twig;

    private KernelInterface $kernel;

    public function __construct(Environment $twig, KernelInterface $kernel)
    {
        $this->twig = $twig;
        $this->kernel = $kernel;
    }

    public function uploadImage(UploadedFile $imageFile, SluggerInterface $slugger, $directoryName)
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        // Using Slug for safe
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
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

    public function generateInvoicePdf($order)
    {
        $directoryName = 'pdf_invoices_directory';

        if (!is_dir($this->getParameter($directoryName))) {
            mkdir($this->getParameter($directoryName), 0777, true);
        }

        // Logo Image Behavior 

        $publicPath = $this->kernel->getProjectDir() . '/public';
        $logoPath = $publicPath . '/images/logo/marrymix-detailed.png';

        // HTML Content
        $htmlContent = $this->twig->render('pdf/invoice_template.html.twig', [
            'order' => $order,
            'logoPath' => $logoPath,
        ]);

        // Options Behavior 
        $options = new Options();
        $options->set('defaultFont', 'serif');
        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('chroot', $publicPath);
        $options->set('tempDir', $publicPath);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        // Initialize DomPDF
        $dompdf = new Dompdf($options);

        // Path Behavior 
        $dompdf->setHttpContext($context);

        // Format PDF
        $dompdf->setPaper('A4', 'portrait');

        $dompdf->setBasePath($publicPath);

        // Upload Content
        $dompdf->loadHtml($htmlContent);

        // Render PDF
        $dompdf->render();

        // PDF Name
        $fileName = 'invoice_' . uniqid() . '.pdf';

        // Save PDF 
        $pdfPath = $this->getParameter($directoryName) . '/' . $fileName;
        file_put_contents($pdfPath, $dompdf->output());

        return $pdfPath;
    }

    public function downloadInvoicePdf($order)
    {
        // Generate Invoice PDF
        $pdfPath = $this->generateInvoicePdf($order);

        // Response For Downloading PDF
        $response = new Response();
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "Invoice_MarryMix_" . date('Y-m-d') . ".pdf"
        ));
        $response->setContent(file_get_contents($pdfPath));

        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        return $response;
    }
}
