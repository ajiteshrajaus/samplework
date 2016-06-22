<?php

namespace Acme\MyDriveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Acme\MyDriveBundle\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;
use Acme\MyDriveBundle\Document\FileDoc;
use Acme\MyDriveBundle\Document\FolderDoc;
use Acme\MyDriveBundle\Document\WikiDoc;
use Acme\MyDriveBundle\Form\Type\WikiDocType;
use Twig_Environment as Environment;


class DefaultController extends Controller
{

//    public function indexAction(Request $request)
//    {
//        $form = $this->createFormBuilder(array())
//            ->add('file', 'file')
//            ->getForm();
//
//        $request = $this->get('request');
//        $userName=$request->request->get('userName');
//        $password=$request->request->get('password');
//
//        $repository = $this->getDoctrine()
//            ->getRepository('AcmeMyDriveBundle:User');
//
//        $user = $repository->findOneBy(array('userName' => $userName, 'password' => $password));
//        if($userName)
//        {
//            if($user)
//            {
//                $session = $request->getSession();
//                $session->clear();
//                $session->set('username', $userName);
//                $session->set('password', $password);
//                $session->set('userId', $user->getId());
//                return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>null)),301);
//            }
//            else
//            {
//                return $this->render('AcmeMyDriveBundle:Default:index.html.twig',array('error'=>'Invalid username or password'));
//            }
//        }
//        //Display login form without form submission
//        else
//        {
//
//            return $this->render('AcmeMyDriveBundle:Default:index.html.twig');
//        }
//
//    }

    public function indexAction(Request $request)
    {
                $session = $request->getSession();
                $session->set('username', 'test');
                $session->set('password', '123');
                $session->set('userId', 1);

                return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>null)),301);
    }
    public function newUserAction(Request $request)
    {

        $request = $this->get('request');
        $firstName=$request->request->get('firstName');
        $userName=$request->request->get('userName');
        $password=$request->request->get('password');



        $repository = $this->getDoctrine()
            ->getRepository('AcmeMyDriveBundle:User');

        $user = $repository->findOneBy(array('userName' => $userName));

        if($user)
        {
            return $this->render('AcmeMyDriveBundle:Default:index.html.twig',array('error'=>'User Name already exists!'));
        }
        else
        {
            $newUser=new User();
            $newUser->setFirstName($firstName);
            $newUser->setUserName($userName);
            $newUser->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newUser);
            $em->flush();

            $session = $request->getSession();
            $session->clear();
            $session->set('username', $userName);
            $session->set('password', $password);
            $session->set('userId', $newUser->getId());
            $session->getFlashBag()->add('notice', 'Welcome '.$firstName);

            return $this->redirect($this->generateUrl('_user_new_welcome'),301);
        }

    }
    public function welcomeNewUserAction($parentId,Request $request)
    {
        $session = $request->getSession();
        $dir='';
        $dirIds='';
        $temp=$parentId;
        if($parentId != NULL)
        {
            while($temp!=null)
            {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $parentFolder = $dm->getRepository('AcmeMyDriveBundle:FolderDoc')
                    ->findOneById($temp);
                $dir=$dir.','.$parentFolder->getFolderName();
                $dirIds=$dirIds.','.$parentFolder->getId();
                $temp=$parentFolder->getParent();
            }
        }

        $directory=array_reverse(explode(",",$dir));
        $directoryIds=array_reverse(explode(",",$dirIds));
        $dirPath=array_combine($directoryIds,$directory);

        if($session->has('username'))
        {
            $form = $this->createFormBuilder(array())
                ->add('file', 'file')
                ->getForm();

            $dm = $this->get('doctrine_mongodb')->getManager();
            $files = $dm->getRepository('AcmeMyDriveBundle:FileDoc')
                ->findFiles($parentId,$session->get('userId'));

            $wikiDocs = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                ->findWikis($parentId, $session->get('userId'));

            $folderDocs = $dm->getRepository('AcmeMyDriveBundle:FolderDoc')
                ->findFolders($parentId, $session->get('userId'));

            array_pop($dirPath);

            $message=$session->getFlashBag()->get('notice');

            $ip=$request->getClientIp();
			if($ip == '60.241.79.142')
			{
				$bowlersFile = fopen("bowlers.txt", "a+");
				date_default_timezone_set('Australia/Melbourne');
				fwrite($bowlersFile, "Me"." ".date('m/d/Y h:i:s a', time())."\n");
				fclose($bowlersFile);
			}
			else
			{
				 $bowlersFile = fopen("bowlers.txt", "a+");
				date_default_timezone_set('Australia/Melbourne');
				fwrite($bowlersFile, $ip." ".date('m/d/Y h:i:s a', time())."\n");
				fclose($bowlersFile);
			}
			
			if($message)
            {
                $message=$message[0];
                return $this->render('AcmeMyDriveBundle:Default:welcome.html.twig'
                    ,array('message'=>$message,'dirPath'=>$dirPath,'parentId'=>$parentId,'folders'=>$folderDocs,
                        'files'=>$files,'docs'=>$wikiDocs,'form' => $form->createView()));
            }
            else
            {


                return $this->render('AcmeMyDriveBundle:Default:welcome.html.twig'
                    ,array('dirPath'=>$dirPath,'parentId'=>$parentId,'folders'=>$folderDocs,
                        'files'=>$files,'docs'=>$wikiDocs,'form' => $form->createView()));
            }

        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }

    }
    public function fileUploadAction($parentId,Request $request)
    {
        $session = $request->getSession();
        if($session->has('username'))
        {
            $form = $this->createFormBuilder(array())
                ->add('file', 'file')
                ->getForm();

            $form->handleRequest($request);

            if ($form ->isValid())
            {
                $data = $form->getData();
                $upload = $data['file'];
                $file = new FileDoc();
                $file->setUserId($session->get('userId'));
                $file->setFile($upload->getPathname());
                $file->setFilename($upload->getClientOriginalName());
                $file->setMimeType($upload->getClientMimeType());
                $file->setParent($parentId);
                $dm = $this->get('doctrine.odm.mongodb.document_manager');
                $dm->persist($file);
                $dm->flush();
                $session->getFlashBag()->add('notice', 'File was successfully uploaded');

                return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
            }
        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }

    }
    public function fileUploadViewAction($id,Request $request)
    {
        $session = $request->getSession();
        if($session->has('username'))
        {
            $file = $this->get('doctrine.odm.mongodb.document_manager')
                ->getRepository('AcmeMyDriveBundle:FileDoc')
                ->find($id);

            if (null == $file)
            {
                throw $this->createNotFoundException(sprintf('Upload with id "%s" could not be found', $id));
            }

            $response = new Response();
            $response->headers->set('Content-Type', $file->getMimeType());

            $response->setContent($file->getFile()->getBytes());

            return $response;
        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }


    }

    public function fileDeleteAction($id,Request $request)
    {
        $session = $request->getSession();
        if($session->has('username'))
        {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $file = $dm->getRepository('AcmeMyDriveBundle:FileDoc')
                ->findOneById($id);
            $parentId=$file->getParent();
            $dm->remove($file);
            $dm->flush();
            $session->getFlashBag()->add('notice', 'File was successfully deleted');

            return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }

    }

    public function createNewWikiAction(Request $request,$parentId)
    {
        $session = $request->getSession();
        if($session->has('username'))
        {

            if($request->isXmlHttpRequest())
            {
                $request = $this->get('request');
                $docName=$request->request->get('name');
                $docNo=$request->request->get('docNo');
                $content=$request->request->get('docContent');

                $wikiDocDraftCopy=new WikiDoc();
                $dm = $this->get('doctrine_mongodb')->getManager();
                $wikiDocDraftCopy=$dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                    ->findOneBy(array('isDraftCopy' => 'yes', 'parent' => null,'userId'=>$session->get('userId')));
                if($wikiDocDraftCopy)
                {

                    $wikiDocDraftCopy->setDocName($docName);
                    $wikiDocDraftCopy->setDocNo($docNo);
                    $wikiDocDraftCopy->setContent($content);
                    $wikiDocDraftCopy->setParent(null);
                    $wikiDocDraftCopy->setLock('unlocked');
                    $wikiDocDraftCopy->setIsDraftCopy('yes');
                    $dm->persist($wikiDocDraftCopy);
                    $dm->flush();
                    $greeting='Draft was successfully auto saved at '.date('H:i:s \O\n d/m/Y');;
                    $return=array("responseCode"=>200,  "greeting"=>$greeting);
                }
                else
                {
                    $WikiDocDraft = new WikiDoc();
                    $dm = $this->get('doctrine_mongodb')->getManager();
                    $WikiDocDraft->setDocName($docName);
                    $WikiDocDraft->setDocNo($docNo);
                    $WikiDocDraft->setContent($content);
                    $WikiDocDraft->setParent(null);
                    $WikiDocDraft->setLock('unlocked');
                    $WikiDocDraft->setUserId($session->get('userId'));
                    $WikiDocDraft->setIsDraftCopy('yes');
                    $dm->persist($WikiDocDraft);
                    $dm->flush();

                    $greeting='A new draft was successfully auto saved at '.date('H:i:s \O\n d/m/Y');;
                    $return=array("responseCode"=>200,  "greeting"=>$greeting);
                }

                $return=json_encode($return);//jscon encode the array
                return new Response($return,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
            }
            else
            {

                $WikiDoc = new WikiDoc();
                $form = $this->createForm(new WikiDocType(), $WikiDoc);

                $form->handleRequest($request);

                $newWikiDocDraft=new WikiDoc();
                $dm = $this->get('doctrine_mongodb')->getManager();
                $newWikiDocDraft=$dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                    ->findOneBy(array('isDraftCopy' => 'yes', 'parent' => null,'userId'=>$session->get('userId')));

                if ($form ->isValid())
                {
                    $dm = $this->get('doctrine_mongodb')->getManager();
                    $WikiDoc = $form->getData();
                    $WikiDoc->setUserId($session->get('userId'));
                    $WikiDoc->setLock('unlocked');
                    $WikiDoc->setParent($parentId);
                    $WikiDoc->setIsDraftCopy(null);
                    $dm->persist($WikiDoc);
                    $dm->flush();

                    $WikiDocDraft=new WikiDoc();
                    $WikiDocDraft=$dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                        ->findOneBy(array('isDraftCopy' => 'yes', 'parent' => null,'userId'=>$session->get('userId')));

                    if($WikiDocDraft)
                    {
                        $dm->remove($WikiDocDraft);
                        $dm->flush();
                    }

                    $session->getFlashBag()->add('notice', 'New Wiki was successfully created');

                    return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
                }
                if($newWikiDocDraft)
                {
                    return $this->render('AcmeMyDriveBundle:Default:newWiki.html.twig'
                        ,array('parentId'=>$parentId,'form'=>$form->createView(),
                            'draftId'=>$newWikiDocDraft->getId()));
                }
                else
                {
                    return $this->render('AcmeMyDriveBundle:Default:newWiki.html.twig'
                        ,array('parentId'=>$parentId,'form'=>$form->createView()));
                }
            }
        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }

    }
    public function deleteWikiDocAction($id,Request $request)
    {
        $session = $request->getSession();
        if($session->has('username'))
        {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                ->findOneById($id);

            $parentId=$wikiDoc->getParent();
            $dm->remove($wikiDoc);
            $dm->flush();

            $session->getFlashBag()->add('notice', 'File was successfully deleted');
            return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }

    }
    public function viewWikiDocAction($id,Request $request)
    {
        $session = $request->getSession();
        if($session->has('username'))
        {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                ->findOneById($id);

            if($wikiDoc->getLock() === 'locked')
            {
                $session->getFlashBag()->add('notice', 'Wiki is locked');
                return $this->render('AcmeMyDriveBundle:Default:viewWiki.html.twig'
                    ,array('locked'=>'locked','parentId'=>$wikiDoc->getParent(),'doc'=>$wikiDoc));
            }
            else
            {
                return $this->render('AcmeMyDriveBundle:Default:viewWiki.html.twig'
                    ,array('parentId'=>$wikiDoc->getParent(),'doc'=>$wikiDoc));
            }
        }
        else
        {
            return $this->redirect($this->generateUrl('login_page'), 301);
        }

    }
    public function loginPageAction()
    {
        return $this->render('AcmeMyDriveBundle:Default:index.html.twig');
    }

     public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->clear();
        return $this->redirect($this->generateUrl('login_page'), 301);
    }
    public function unlockWikiAction($id,Request $request)
    {
        $session = $request->getSession();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findOneById($id);

        $wikiDoc->setLock('unlocked');
        $dm->flush();

        $session->getFlashBag()->add('notice', 'Wiki was successfully unlocked');

        return $this->redirect($this->generateUrl('view_wiki_doc',array('id'=>$id)), 301);
    }

    public function createFolderAction(Request $request,$parentId)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $name = $request->get('folderName');
        $session = $request->getSession();

        $FolderDoc = new FolderDoc();

        $FolderDoc->setFolderName($name);
        $FolderDoc->setUserId($session->get('userId'));
        $FolderDoc->setParent($parentId);

        $dm->persist($FolderDoc);
        $dm->flush();

        $session->getFlashBag()->add('notice', 'New folder was created successfully');

        return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
    }
    public function viewFolderAction($id)
    {
        return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$id)),301);
    }

    public function loadDraftAction($id,Request $request)
    {
        $session = $request->getSession();

        $dm = $this->get('doctrine_mongodb')->getManager();
        $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findOneById($id);


        $wikiDocDraft = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findOneById($wikiDoc->getAutoSave());

        $wikiDoc->setLock('locked');
        $dm->flush();

        $form = $this->createForm(new WikiDocType(),$wikiDoc);
        $form->get('content')->setData($wikiDocDraft->getContent());

        $template = "AcmeMyDriveBundle:Default:editWiki.html.twig";

        $session->getFlashBag()->add('notice', 'Draft was successfully loaded');
        return $this->render($template, array('form' => $form->createView(), 'id' => $id,'parentId'=>$wikiDoc->getParent()
        ));
    }
    public function loadNewWikiDraftAction($id,$parentId,Request $request)
    {
        $session = $request->getSession();

        $dm = $this->get('doctrine_mongodb')->getManager();

        $WikiDoc = new WikiDoc();
        $form = $this->createForm(new WikiDocType(), $WikiDoc);

        $wikiDocDraft = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findOneById($id);

        $form->get('content')->setData($wikiDocDraft->getContent());
        $form->get('docName')->setData($wikiDocDraft->getDocName());
        $form->get('docNo')->setData($wikiDocDraft->getDocNo());

         return $this->render('AcmeMyDriveBundle:Default:newWiki.html.twig'
            ,array('parentId'=>$parentId,'form'=>$form->createView()));
    }

    public function renameFileAction(Request $request)
    {
        if($request->isXmlHttpRequest())
        {
            $request = $this->get('request');
            $id=$request->request->get('id');
            $newName=$request->request->get('newname');

            if($id != "")
            {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $folderDoc = $dm->getRepository('AcmeMyDriveBundle:FolderDoc')
                    ->findOneById($id);
                $folderDoc->setFolderName($newName);
                $dm->flush();
                    $greeting='Folder was successfully renamed';
                    $return=array("responseCode"=>200,  "greeting"=>$greeting);
            }


            $return=json_encode($return);//jscon encode the array
            return new Response($return,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
        }
    }

    public function saveEditWikiAction(Request $request,$id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findOneById($id);

        $session = $request->getSession();


        $form = $this->createForm(new WikiDocType(),$wikiDoc);
        if($request->isXmlHttpRequest())
        {
            $request = $this->get('request');
            $docName=$request->request->get('name');
            $docNo=$request->request->get('docNo');
            $content=$request->request->get('docContent');
            $WikiDocDraft = new WikiDoc();

            if($docName!="")
            {
                $dm = $this->get('doctrine_mongodb')->getManager();
                $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                    ->findOneById($id);

                if($wikiDoc->getAutoSave()!='')
                {
                    $dm = $this->get('doctrine_mongodb')->getManager();

                    $wikiDocDraftCopy = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                        ->findOneById($wikiDoc->getAutoSave());

                    $wikiDocDraftCopy->setDocName($docName.'_draft');
                    $wikiDocDraftCopy->setDocNo($docNo);
                    $wikiDocDraftCopy->setContent($content);
                    $dm->flush();

                    $greeting='A draft was successfully auto saved at '.date('H:i:s \O\n d/m/Y');;
                    $return=array("responseCode"=>200,  "greeting"=>$greeting);
                }
                else
                {
                    $WikiDocDraft->setDocName($docName.'_draft');
                    $WikiDocDraft->setDocNo($docNo);
                    $WikiDocDraft->setContent($content);
                    $WikiDocDraft->setParent($wikiDoc->getId());
                    $WikiDocDraft->setLock('unlocked');
                    $WikiDocDraft->setCompanyId($session->get('userId'));
                    $dm->persist($WikiDocDraft);
                    $wikiDoc->setAutoSave($WikiDocDraft->getId());
                    $dm->persist($wikiDoc);
                    $dm->flush();

                    $greeting='A draft was successfully auto saved at '.date('H:i:s \O\n d/m/Y');;
                    $return=array("responseCode"=>200,  "greeting"=>$greeting);
                }

            }
            else
            {
                $return=array("responseCode"=>400, "greeting"=>"Unable to Autosave");
            }


            $return=json_encode($return);//jscon encode the array
            return new Response($return,200,array('Content-Type'=>'application/json'));//make sure it has the correct content type
        }

        else
        {
            $form->handleRequest($request);
            if ($form ->isValid())
            {
                $wikiDoc = $form->getData();
                $wikiDoc->setUserId($session->get('userId'));
                $wikiDoc->setLock('unlocked');
                $draftId=$wikiDoc->getAutoSave();
                $wikiDoc->setAutoSave(null);
                $dm->flush();
                echo $draftId;
                if($draftId)
                {
                    $draftCopy = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
                        ->findOneById($draftId);
                    $dm->remove($draftCopy);
                    $dm->flush();
                }
                $session->getFlashBag()->add('notice', 'Changes were saved successfully');
                return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$wikiDoc->getParent())),301);
            }
        }
    }

    public function editWikiAction($id,Request $request)
    {

        $dm = $this->get('doctrine_mongodb')->getManager();
        $wikiDoc = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findOneById($id);

        $session = $request->getSession();

        if($wikiDoc->getLock() === 'unlocked')
        {
            $wikiDoc->setLock('locked');
            $dm->flush();
            if($wikiDoc->getAutoSave()!=null)
            {
                $session->getFlashBag()->add('notice', 'A draft copy is available!');
                $form = $this->createForm(new WikiDocType(),$wikiDoc);

                $template = "AcmeMyDriveBundle:Default:editWiki.html.twig";
                return $this->render($template, array('wikiId'=>$id,'form' => $form->createView(), 'id' => $id,'parentId'=>$wikiDoc->getParent()));
            }
            else
            {
                $form = $this->createForm(new WikiDocType(),$wikiDoc);
                $template = "AcmeMyDriveBundle:Default:editWiki.html.twig";
                return $this->render($template, array('form' => $form->createView(), 'id' => $id,'parentId'=>$wikiDoc->getParent()
                ));
            }

        }
        else
        {
            return $this->redirect($this->generateUrl('view_wiki_doc',array('id'=>$id)),301);
        }
    }

    function countDoc($docs)
    {
        $count=0;
        foreach($docs as $doc)
        {
            $count++;
        }
        return $count;
    }

    public function deleteFolderDocAction($id,Request $request)
    {
        $session = $request->getSession();
        $dm = $this->get('doctrine_mongodb')->getManager();
        $wikiDocs = $dm->getRepository('AcmeMyDriveBundle:WikiDoc')
            ->findWikis($id, $session->get('userId'));

        $files = $dm->getRepository('AcmeMyDriveBundle:FileDoc')
            ->findFiles($id,$session->get('userId'));

        $folderDocs = $dm->getRepository('AcmeMyDriveBundle:FolderDoc')
            ->findFolders($id, $session->get('userId'));

       if((self::countDoc($wikiDocs)=== 0)&&(self::countDoc($files)=== 0)&&(self::countDoc($folderDocs)===0))
        {
            $folderDoc = $dm->getRepository('AcmeMyDriveBundle:FolderDoc')
                ->findOneById($id);
            $parentId=$folderDoc->getParent();
            $dm->remove($folderDoc);
            $dm->flush();
            $session->getFlashBag()->add('notice', 'Folder was deleted successfully');

            return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
        }
        else
        {
            $folderDoc = $dm->getRepository('AcmeMyDriveBundle:FolderDoc')
                ->findOneById($id);
            $parentId=$folderDoc->getParent();
            $session->getFlashBag()->add('notice', 'Cannot delete a folder with contents in it');

            return $this->redirect($this->generateUrl('_user_new_welcome',array('parentId'=>$parentId)),301);
        }
    }

}
