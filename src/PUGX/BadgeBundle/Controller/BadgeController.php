<?php

namespace PUGX\BadgeBundle\Controller;

use PUGX\BadgeBundle\Service\ImageCreator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BadgeController extends Controller
{

    public function downloadsAction($vendor, $repository, $type = 'total')
    {
        $imageCreator = $this->get('image_creator');
        $repository = $vendor . '/' . $repository;
        $outputFilename = sprintf('%s.png', $type);
        $httpCode = 200;

        // get the statistic from packagist
        try {
            $downloads = $this->get('badger')->getPackageDownloads($repository, $type);
        } catch (\Exception $e){
            $downloadsText = ImageCreator::ERROR_TEXT_CLIENT_EXCEPTION;
            $httpCode = 500;
        }

        // and then makes it readable
        try {
            $downloadsText = $imageCreator->transformNumberToReadableFormat($downloads);
        } catch (\Exception $e){
            $downloadsText = ImageCreator::ERROR_TEXT_GENERIC;
            $httpCode = 500;
        }

        // handles the image
        $image = $imageCreator->createDownloadsImage($downloadsText);
        //generating the streamed response
        $response = new StreamedResponse(null, $httpCode);
        $response->setCallback(function () use ($imageCreator, $image) {
            $imageCreator->streamRawImageData($image);
        });
        $response->headers->set('Content-Type', 'image/png');
        $response->headers->set('Content-Disposition', 'inline; filename="'.$outputFilename.'"');
        $response->send();
        $imageCreator->destroyImage($image);

        return $response;
    }
}