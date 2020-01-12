<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\JobRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CommentsController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

    }

    /**
     * @Route("/add/comment/{id}", name="add.comment", methods={"POST"})
     * @param Request $req
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function add(Request $req, Security $security,$id, ProjectRepository $projectRepo, JobRepository $jobRepo)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $referer = explode('/',$req->headers->get('referer'));
            $com = $form->getData();
            //dd($jobRepo->find($id));
            if($referer[3] === "job"){
                $post= $jobRepo->find($id);
                $com->setUser($security->getUser())
                    ->setJob($post)
                    ->setCreatedAt(new \DateTime())
                    ->setApproved(false);
                $this->em->persist($com);
                $this->em->flush();

                return $this->redirectToRoute('job.show', array('slug'=>$post->getSlug()));
            }else if($referer[3] === "project"){
                $post= $projectRepo->find($id);
                $com->setUser($security->getUser())
                    ->setProject($post)
                    ->setCreatedAt(new \DateTime())
                    ->setApproved(false);
                $this->em->persist($com);
                $this->em->flush();

                return $this->redirectToRoute('project.show', array('slug'=>$post->getSlug()));
            }




            //dd($com);


        }
    }

    /**
     * @Route("/delete/comment/{comment}", name="delete.comment", methods={"GET"})
     */
    public function delete(Comment $comment, Security $security,Request $req)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if($security->getUser() === $comment->getUser()){
            $referer = explode('/',$req->headers->get('referer'));
            //dd($jobRepo->find($id));
            if($referer[3] === "job"){
                $post = $comment->getJob();
                $this->em->remove($comment);
                $this->em->flush();
                return $this->redirectToRoute('job.show', array('slug'=>$post->getSlug()));

            }
            else if($referer[3] === "project"){
                $post = $comment->getProject();
                $this->em->remove($comment);
                $this->em->flush();
                return $this->redirectToRoute('project.show', array('slug'=>$post->getSlug()));

            }

        }
        return $this->redirectToRoute('job.show', array('slug'=>$post->getSlug()));

        //dd($comment);

    }

    /**
     * @Route("/comment/edit/{comment}", name="comment_edit")
     */
    public function edit(Comment $comment,Request $request){

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
           // dd($form);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setTextComment($form["textComment"]->getData());
            $this->em->flush();

        }
        return new Response;
    }

}
