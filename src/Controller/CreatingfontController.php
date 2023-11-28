<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Fonts;
use App\Entity\FormatFonts;
use Symfony\Component\Security\Core\User\UserInterface;
use ZipArchive;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class CreatingfontController extends AbstractController
{
    /**
     * @Route(path="/api/font_creating", name="admin_create_font",
     *     methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function index(Request $request): Response
    {
        $date =  new \DateTime();
        $font_family = $request->get('font_family');
        $font_weight = $request->get('font_weight');
        $filename = $request->get('font_folder');
        $font_style = $request->get('font_style');

        if(!isset($filename) || $filename == ''){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Font folder is not existed']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if(!isset($font_family) || $font_family == ''){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Font family is not existed']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if(!isset($font_weight) || $font_weight == ''){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Font weight is not existed']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if(!isset($font_style) || $font_style == ''){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Font style is not existed']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        $file = $request->files->get('file');

        if($file != null){
            if($file->guessExtension() != 'zip'){
                $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong type of data, Zip must be']]);
                $json_response->setStatusCode(402);
                return $json_response;
            }
        } else {
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Error, please upload another file']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if(is_dir($this->getParameter('font_directory').'/'.$filename)) {
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Папка с таким названием существует']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        mkdir($this->getParameter('font_directory').'/'.$filename);

        $zip = new ZipArchive();
        $zip->open($file);
        $zip->extractTo($this->getParameter('font_directory').'/'.$filename);
        $zip->close();
        $new_font = new Fonts();
        $new_font->setFontFamily($font_family);
        $new_font->setFontWeight($font_weight);
        $new_font->setFolder($filename);
        $new_font->setFontStyle($font_style);
        $new_font->setCreatedAt($date);
        $new_font->setUpdatedAt($date);
        $em = $this->getDoctrine()->getManager();
        $em->persist($new_font);
        $em->flush();
        $css_content = '@font-face {font-family: "'.$font_family.'";';

        $files = scandir($this->getParameter('font_directory').'/'.$filename);
        foreach ($files as $file_item)
        {
            if ($file_item != '.' && $file_item != '..') {
                $format_type = $this->getExt($file_item);
                $new_format = new FormatFonts();
                $new_format->setTitle($this->getFile($file_item));
                $new_format->setFont($new_font);
                $new_format->setUrl($this->getParameter('domain').'/fonts/'.$filename.'/'.$file_item);
                if($format_type != ''){
                    $em->persist($new_format);
                    $em->flush();
                }

                $css_content .= ' url("../../assets/fonts/'.$filename.'/'.$file_item.'") format("'.$this->getExt($file_item).'"),';
            }
        }
        $css_content .= '; font-weight: '.$font_weight.';font-style: '.$font_style.';}';
        file_put_contents($this->getParameter('font_directory').'/'.$filename.'/font.css', $css_content);
        //unlink($this->getParameter('font_directory').'/'.$filename.'/font.css')
        $json_response = new JsonResponse(['data' =>['code' => 200, 'message' => 'Шрифт успешно создан']]);
        return $json_response;
    }

    private function getExt($filename){
        if(strpos($filename, '.otf')) return 'opentype';
        if(strpos($filename, '.ttf')) return 'truetype';
        if(strpos($filename, '.woff')) return 'woff';
        if(strpos($filename, '.woff2')) return 'woff2';
        return '';
    }


    private function getFile($filename){
        if(strpos($filename, '.otf')) return 'otf';
        if(strpos($filename, '.ttf')) return 'ttf';
        if(strpos($filename, '.woff')) return 'woff';
        if(strpos($filename, '.woff2')) return 'woff2';
        return '';
    }

}
