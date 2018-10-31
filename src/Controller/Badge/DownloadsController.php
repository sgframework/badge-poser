<?php

/*
 * This file is part of the badge-poser package.
 *
 * (c) PUGX <http://pugx.github.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Badge;

use App\Badge\Infrastructure\ResponseFactory;
use App\Badge\Model\UseCase\CreateDownloadsBadge;
use App\Badge\Service\ImageFactory;
use PUGX\Poser\Poser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DownloadsController
 * Download action for badges.
 */
class DownloadsController extends Controller
{
    /**
     * Downloads action.
     *
     * @param Request              $request
     * @param Poser                $poser
     * @param CreateDownloadsBadge $createDownloadsBadge
     * @param ImageFactory         $imageFactory
     * @param string               $repository           repository
     * @param string               $type                 badge type
     * @param string               $format
     *
     * @return Response
     * @Cache(maxage="3600", smaxage="3600", public=true)
     *
     * @throws \InvalidArgumentException
     */
    public function downloads(
        Request $request,
        Poser $poser,
        CreateDownloadsBadge $createDownloadsBadge,
        ImageFactory $imageFactory,
        $repository,
        $type,
        $format = 'svg'
    ): Response {
        if (\in_array($request->query->get('format'), $poser->validFormats(), true)) {
            $format = $request->query->get('format');
        }

        $badge = $createDownloadsBadge->createDownloadsBadge($repository, $type, $format);
        $image = $imageFactory->createFromBadge($badge);

        return ResponseFactory::createFromImage($image, 200);
    }
}