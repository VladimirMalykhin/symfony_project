<?php

namespace App\Controller;

use App\Entity\Epacks;
use App\Entity\UserStatisticGroup;
use App\Service\EpackageCleaner\EpackageCleaner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpClient\HttpClient;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use ZipArchive;


class AdminLoadPanelController extends AbstractController
{
    //git test2
    /**
     * @Route(path="/api/load/panel/{number}", name="admin_load_panel",
     *     requirements={
     *          "number" = "%app.id_integer_regex%",
     *      },
     *     methods={"POST"})
     */
    public function index(string $number, Request $request, UserInterface $user): Response
    {
        
        $roles = $user->getRoles();
        if($roles[0] != 'admin'){
            $em = $this->getDoctrine()->getManager()->createQueryBuilder();
            $dql_query = $em->select('e')
                ->from('App\Entity\Epacks', 'e');
            $group = $this->getDoctrine()
                ->getRepository(UserStatisticGroup::class)
                ->findOneBy([
                    'user' => $user->getId()]);
            if($group){
                $list_users = $this->getDoctrine()
                    ->getRepository(UserStatisticGroup::class)
                    ->findBy([
                        'statisticGroup' => $group->getStatisticGroup()]);
                $users = [];
                foreach($list_users as $user_item){
                    $users[] = $user_item->getUser();
                }
            } else {
                $users = [];
                $users[] = $user;
            }
            $dql_query = $dql_query->andWhere('e.user IN (:users)')
                ->andWhere('e.file = :number')
                ->setParameter(':number', $number)
                ->setParameter(':users', $users);
            $epacks = $dql_query->getQuery()->getOneOrNullResult();
        }
        else{
            $epacks = $this->getDoctrine()
                ->getRepository(Epacks::class)
                ->findOneBy([
                    'file' => $number
                ]);
        }
        if(!$epacks){
            throw new NotFoundHttpException('No such E-pack');
        }
		$contentRequest = (array)json_decode($request->getContent(), true);
        $components = json_decode($epacks->getStructure(), true);
        if(file_exists($this->getParameter('epacks_directory').'/'.$number.'/ru/minisite/mvideo_template/content/json/index.json')){
            $cover_data = file_get_contents($this->getParameter('epacks_directory').'/'.$number.'/ru/minisite/mvideo_template/content/json/index.json');

        }
        if(isset($cover_data)){
            $cleaning = new EpackageCleaner($number, $this->getParameter('epacks_directory'), $components, $cover_data);
        } else {
            $cleaning = new EpackageCleaner($number, $this->getParameter('epacks_directory'), $components, '');
        }
        $cleaning->clean_epackage();
        $this->zip($this->getParameter('epacks_directory').'/'.$number, $this->getParameter('temp_directory').'/archives/'.$number.'.zip');
        $httpClient = HttpClient::create();
        $response = $httpClient->request('POST', $this->getParameter('panel_url').'', [
            'json'  => [
                'username' => '',
                'password' => ''
            ]
        ]);

        $token = $response->toArray()['token'];

        $formFields = [
            'archive' => [
                'archiveFile' => DataPart::fromPath($this->getParameter('temp_directory').'/archives/'.$number.'.zip'),
            ]
        ];

		if(isset($contentRequest['license'])) $formFields['archive']['licenseId'] = $contentRequest['license'];
        $formData = new FormDataPart($formFields);

        $options = [
            'headers' => array_merge($formData->getPreparedHeaders()->toArray(), [
                'Authorization' => 'Bearer '.$token,
            ]),
            'body' => $formData->bodyToIterable(),
        ];
        $response = $httpClient->request('POST',   $this->getParameter('panel_url').'', $options);
		
		$responseArray = json_decode($response->getContent(false), true);
		
			
        if($responseArray['code'] == 409){
            $json_response = new JsonResponse(['data' =>['code' => 409, 'message' => $responseArray['data']]]);
            $json_response->setStatusCode(409);
            return $json_response;
        }
        if($responseArray['code'] == 401){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => $responseArray['error']]]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
        if($responseArray['code'] == 400){
            $json_response = new JsonResponse(['data' =>['code' => 402, 'message' => $responseArray['error']]]);
            $json_response->setStatusCode(402);
            return $json_response;
        }
		
		if($responseArray['code'] == 200){
			$em = $this->getDoctrine()->getManager();
			$epacks = $this->getDoctrine()
				->getRepository(Epacks::class)
				->findOneBy([
					'file' => $number
				]);
			$epacks->setIsUpdated(true);
			$epacks->setStatus(2);
			$em->persist($epacks);
			$em->flush();
		}
        return new JsonResponse(['data' => ['code' => 200, 'message' => $response]]);
    }


    public function zip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', DIRECTORY_SEPARATOR, realpath($source));
        $source = str_replace('/', DIRECTORY_SEPARATOR, $source);

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
                $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

                if ($file == '.' || $file == '..' || empty($file) || $file == DIRECTORY_SEPARATOR) {
                    continue;
                }
                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, DIRECTORY_SEPARATOR) + 1), array('.', '..'))) {
                    continue;
                }

                $file = realpath($file);
                $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
                $file = str_replace('/', DIRECTORY_SEPARATOR, $file);

                if (is_dir($file) === true) {
                    $d = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
                    if (empty($d)) {
                        continue;
                    }
                    $zip->addEmptyDir($d);
                } elseif (is_file($file) === true) {
                    $zip->addFile($file, str_replace($source . DIRECTORY_SEPARATOR, '', $file));
                } else {
                    // do nothing
                }
            }
        } elseif (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
        $zip->close();
    }
}
