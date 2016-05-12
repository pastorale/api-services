<?php
namespace AppBundle\Services\Organisation\Handbook;

use AppBundle\Entity\Organisation\Handbook\Content;
use AppBundle\Services\Core\Framework\BaseController;
use Doctrine\Common\Collections\Criteria;

class ContentManipulator extends BaseController
{
    private function deleteContentMedia($fieldName, $content)
    {
        $mediaManager = $this->container->get('sonata.media.manager.media');
        $mediaList = $mediaManager->findBy([$fieldName => $content->getId()]);
        if ($mediaList !== null) {
            foreach ($mediaList as $media) {
                $mediaManager->delete($media);
            }
        }
    }

    public function deleteContent(Content $content)
    {
        $mediaManager = $this->container->get('sonata.media.manager.media');
        $request = $this->getRequest();
        $locale = $request->get('locale', $request->getLocale());
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');

        $this->deleteContentMedia('imageHandbookContent',$content);
        $this->deleteContentMedia('pdfHandbookContent',$content);

//        $translations = $repository->findTranslations($content);
//        if (isset($translations[$locale])) {
//            $contentWithLocale = $translations[$locale];
//            if (array_key_exists('imageId', $contentWithLocale)) {
//                $imageId = $contentWithLocale['imageId'];
//                if ($imageId !== null) {
//                    $image = $mediaManager->find($imageId);
//                    $mediaManager->delete($image);
//                }
//            }
//            if (array_key_exists('pdfId', $contentWithLocale)) {
//                $pdfId = $contentWithLocale['pdfId'];
//                if ($pdfId !== null) {
//                    $pdf = $mediaManager->find($pdfId);
//                    if ($pdf !== null) {
//                        $mediaManager->delete($pdf);
//                    }
//                }
//            }
//        }
//
//        $imagId = $content->getImageId();
//        if ($imagId !== null) {
//
//            $mediaIMG = $mediaManager->find($imagId);
//            if ($mediaIMG !== null) {
////                $this->handleManipulation($mediaIMG);
//                $mediaManager->delete($mediaIMG);
//            }
//        }
//
//        $pdfId = $content->getPdfId();
//        if ($pdfId !== null) {
//            $mediaPDF = $mediaManager->find($pdfId);;//$em->getRepository('ApplicationSonataMediaBundle:Media')->find($pdfId);
//            if ($mediaPDF !== null) {
//                $mediaManager->delete($mediaPDF);
//            }
//        }

        return $this->handleManipulation($content);
    }
}