<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Epacks;
use Symfony\Component\HttpFoundation\Request;

class LocalesCreateController extends AbstractController
{
    /**
     * @Route(path="api/locale/create", name="app_locales_create",
     *     methods={"POST"})
     */
    public function index(Request $request): Response
    {
        $content = json_encode($request->getContent());
        $response = (array)json_decode($request->getContent(), true);
        if(!$response['locale_name'] || !$response['locale_source'] || !$response['epackages'] || $response['locale_source'] == $response['locale_name']){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => 'Wrong format']]);
            $json_response->setStatusCode(402);
            return $json_response;
        }

        $em = $this->getDoctrine()->getManager()->createQueryBuilder();

        $countDone = 0;
        $epackagesDone = [];
        
        foreach($response['epackages'] as $epackage){
            
            $epackageQuery = $em->select('e')
                ->from('App\Entity\Epacks', 'e')
                ->andWhere('e.file = :epackage')
                ->setParameter(':epackage', $epackage);
            $epackage = $epackageQuery->getQuery()->getOneOrNullResult();
            $emInserted = $this->getDoctrine()->getManager();
            
        
            $manifest = (array)json_decode($epackage->getManifest(), true);
            $structure = (array)json_decode($epackage->getStructure(), true);
            if(!is_dir($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_name'])){
                if(isset($manifest['data'][$response['locale_source']]) && isset($manifest['data'][$response['locale_source']]['minisite']['master_template'])){
                    if(is_dir($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_source'])){
                        if(is_dir($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_source'].'/minisite/master_template')){
                            mkdir($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_name']);
                            mkdir($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_name'].'/minisite');
                            $this->full_copy($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_source'].'/minisite/master_template', $this->getParameter('epacks_directory').'/'.$epackage->getFile().'/' .$response['locale_name']. '/minisite/master_template');
                            $manifest['data'][$response['locale_name']]['minisite']['master_template'] = $manifest['data'][$response['locale_source']]['minisite']['master_template'];
                            $structure[$response['locale_name']]['master_template'] = $structure[$response['locale_source']]['master_template'];
                            $structureJson = json_encode($structure[$response['locale_name']]['master_template']);
                            $structureJson = str_replace('\/'.$response['locale_source'].'\/', '\/'.$response['locale_name'].'\/', $structureJson);
                            $structureJson = json_decode($structureJson, true);
                            $structure[$response['locale_name']]['master_template'] = $structureJson;
                            $epackage->setManifest(json_encode($manifest));
                            $epackage->setStructure(json_encode($structure));
                            $emInserted->persist($epackage);
                            $emInserted->flush(); 
                            file_put_contents($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/manifest.json', json_encode($manifest));
                            $contentJson = str_replace('\/'.$response['locale_source'].'\/', '\/'.$response['locale_name'].'\/', file_get_contents($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_source'].'/minisite/master_template/content/json/index-helper.json'));
                            file_put_contents($this->getParameter('epacks_directory').'/'.$epackage->getFile().'/'.$response['locale_name'].'/minisite/master_template/content/json/index-helper.json', $contentJson);
                            
                            $countDone++;
                            $epackagesDone[] = $epackage->getFile();
                        }
                    }
                }
                
            }
            
        }

        $json_response = new JsonResponse(['data' =>['code' => 200, 'message' => 'Locales have been created', 'epackages_count' => $countDone, 'epackages_list' => $epackagesDone]]);

        return $json_response;

    }


    public function full_copy($source, $target) {
        if (is_dir($source))  {
          @mkdir($target);
          $d = dir($source);
          while (FALSE !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') continue;
            $Entry = $source . '/' . $entry;
            if (is_dir($Entry)) $this->full_copy($Entry, $target . '/' . $entry);
            else copy($Entry, $target . '/' . $entry);
          }
          $d->close();
        }
        else copy($source, $target);
      }
}
