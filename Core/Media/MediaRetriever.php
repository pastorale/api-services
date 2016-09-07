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

//    public function getPublicURL(Media $media)
//    {
//        $provider = $this->get('sonata.media.provider.file');
//        $dir = $this->get('sonata.media.adapter.filesystem.s3')->getDirectory();
//        $cdnPath = str_replace('http', 'https', $provider->getCdnPath($dir . '/' . $provider->generatePath($media), true));
//        $fileName = $media->getProviderReference();
//        return $cdnPath . '/' . $fileName;
//    }
    public function getPublicURL(Media $media,$context='default',$format='reference')
    {
        return 'https://s3-ap-southeast-1.amazonaws.com/magenta-consulting.com/live//0001/01/08b789b9143c5e53b257dd6b49a1d933c9516222.jpeg';
        $provider = $this->get('sonata.media.provider.image');
        $urlNotTrue =  $provider->generatePublicUrl($media,$format);
        $dir = $this->getParameter('s3_directory');
        $region = $this->getParameter('s3_region');
        $host = 'https://s3-'.$region.'.amazonaws.com';
        $bucket = $this->getParameter('s3_bucket_name');


        $arr= explode($bucket,$urlNotTrue);
        $endUrl =  $arr[1];
        return $host.'/'.$bucket.'/'.$dir .'/'. $endUrl;

    }


}
