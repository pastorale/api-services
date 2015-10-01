<?php
// src/Solutions/AppBundle/Services/Core/Media/MediaRetriever.php

namespace AppBundle\Services\Core\Media;

use AppBundle\Services\Core\Framework\BaseController;
use Sonata\MediaBundle\Model\Media;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class MediaRetriever extends BaseController
{

    /**
     * @param int $id
     * @return Media
     */
    public function findOneById($id)
    {
        $mediaManager = $this->get('sonata.media.manager.media');
        return $mediaManager->findOneBy(array('id' => $id));
    }

    public function getPublicURL(Media $media)
    {
        $provider = $this->get('sonata.media.provider.file');
        $dir = $this->get('sonata.media.adapter.filesystem.s3')->getDirectory();
        $cdnPath = $provider->getCdnPath($dir . '/' . $provider->generatePath($media), true);
        $fileName = $media->getProviderReference();
        return $cdnPath . '/' . $fileName;
    }


}
