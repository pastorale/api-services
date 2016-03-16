<?php
// src/Solutions/AppBundle/Services/Core/Media/MediaManipulator.php

namespace AppBundle\Services\Core\Media;

use AppBundle\Services\Core\Framework\BaseController;
use Application\Sonata\MediaBundle\Entity\Media;
use Application\Sonata\MediaBundle\Entity\MediaProcessingInterface;
use FOS\RestBundle\View\View;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MediaManipulator extends BaseController
{
    public function duplicateMedium(Media $medium)
    {
        $mediaManager = $this->get('sonata.media.manager.media');
        $newMedium = clone $medium;

//        $newMedium->setOrganisationOwner($medium->getOrganisationOwner());
//        $newMedium->setUserOwner($medium->getUserOwner());
//        $newMedium->setAppImageOrganisation($medium->getAppImageOrganisation());
//        $newMedium->setProcessed($medium->isProcessed());
//        $newMedium->setAudio($medium->isAudio());
//        $newMedium->setAuthorName($medium->getAuthorName());

        return $newMedium;
    }

    /**
     * @param string $providerName
     * @param null $allowedContentType
     * @return View|mixed|object|Media
     */
    public function handleMediumPost($providerName = 'sonata.media.provider.file', $allowedContentType = null)
    {
        $mediaManager = $this->get('sonata.media.manager.media');
        $media = $mediaManager->create();
        $media->setProviderName($providerName);
        $mediaProvider = $this->get('sonata.media.pool')->getProvider($providerName);
        $form = $this->get('form.factory')->createNamed(null, 'sonata_media_api_form_media', $media, array(
            'provider_name' => $mediaProvider->getName(),
        ));

        $request = $this->getRequest();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $media = $form->getData();
            $contentType = $media->getContentType();
            if ($allowedContentType !== null) {
//                if ( $contentType !== 'application/octet-stream' && $contentType !== 'audio/x-wav') {
                if (!in_array($contentType, $allowedContentType)) {
                    return $this->returnMessage('please submit a correct video format', 400);
                }
            }
            if ($contentType === 'application/octet-stream') {
                $media->setVideo(true);
                $media->setAudio(false);
            } elseif ($contentType === 'audio/x-wav') {
                $media->setVideo(false);
                $media->setAudio(true);
            }
            $media->setPhoto(false);
            $media->setEnabled(true);
            return $media;
        } else {
            return View::create($form, 400);
        }
    }

    public function mergeAudio(MediaProcessingInterface $processing)
    {
        $medium = $processing->getMedia();
        $extra = $processing->getExtraMedia();
        if ($medium->getContentType() == 'application/octet-stream' && $extra->getContentType() == 'audio/x-wav') {
            $mediumURL = $this->get('app.core.media.retriever')->getPublicURL($medium);
            $extraURL = $this->get('app.core.media.retriever')->getPublicURL($extra);
            $fileName = $this->container->getParameter('kernel.root_dir') . '/../web/tmp/' . $this->container->get('app.core.core.generator')->generateRandomString() . '.mp4';
            // http://s3-ap-southeast-1.amazonaws.com/magenta-consulting.com/local/test.webm/0001/01/ea8c06617cd052dcaab48d6a18a10f2413f3c7cd.bin
            // "http://s3-ap-southeast-1.amazonaws.com/magenta-consulting.com/local/test.webm/0001/01/6966ce658347d31c1f80ae8fa4cf63b8de8e0ae3.wav"
            $cmdStr = 'ffmpeg -i ' . $mediumURL . ' -i ' . $extraURL . ' -strict experimental  -map 0:0 -map 1:0 -r 24 ' . $fileName;
            $process = new Process($cmdStr);
            $process->run();

            /**
             * ffmpeg -i http://s3-ap-southeast-1.amazonaws.com/magenta-consulting.com/local/test.webm/0001/01/ea8c06617cd052dcaab48d6a18a10f2413f3c7cd.bin -i "http://s3-ap-southeast-1.amazonaws.com/magenta-consulting.com/local/test.webm/0001/01/6966ce658347d31c1f80ae8fa4cf63b8de8e0ae3.wav" -strict experimental  -map 0:0 -map 1:0 -r 24   output6.mp4
             */
// executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

//            $x = $process->getOutput();
//            $y = $x;

            $medium->setBinaryContent($fileName);
            $mediaManager = $this->get('sonata.media.manager.media');
            $mediaManager->save($medium);
            $processing->setProcessed(true); //$galleryMedia->setProcessed(true);
//            $processing->setExtraMedia(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($processing);
            $em->flush($processing);
            unlink($fileName);
            $mediaManager->delete($extra);
        }
    }


}
