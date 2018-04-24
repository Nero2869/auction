<?php

namespace AppBundle\Controller;



use AppBundle\Form\AuctionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Auction;

class AuctionController extends Controller
{
    /**
     * @Route("/" , name="auction_index")
     *
     * @return Response
     */
    public function indexAction(){

        $entityManager = $this->getDoctrine()->getManager();

        $auctions = $entityManager->getRepository(Auction::class)->findAll();

       return $this->render("Auction/index.html.twig",["auctions" => $auctions]);
    }

    /**
     * @Route("/auction/details/{id}", name="auction_details")
     *
     * @param Auction $auction
     * @return Response
     */
    public function detailsAction(Auction $auction){

        $formDelete = $this->createFormBuilder()
                            ->add("submit", SubmitType::class, ["label" => "Usuń"])
                            ->setMethod(Request::METHOD_DELETE)
                            ->setAction($this->generateUrl("auction_delete", ["id" => $auction->getId()]))
                            ->getForm();

        $finishForm = $this->createFormBuilder()
                            ->setMethod(Request::METHOD_POST)
                            ->setAction($this->generateUrl("auction_finish", ["id"=>$auction->getId()]))
                            ->add("submit", SubmitType::class, ["label" => "Zakończ"])
                            ->getForm();

        return $this->render(
            "Auction/details.html.twig",
            [
                "details" => $auction,
                "deleteForm" => $formDelete->createView(),
                "finishForm" => $finishForm->createView()
            ]
        );
    }

    /**
     * @Route("/auction/add", name="auction_add")
     *
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request){

        $auction = new Auction();

        $form = $this->createForm(AuctionType::class, $auction);

        if($request->isMethod("post")){
            $form->handleRequest($request);

            $auction->setStatus(Auction::STATUS_ACTIVE);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush();

            return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
        }

        return $this->render("Auction/add.html.twig", ["form"=>$form->createView()]);
    }

    /**
     * @Route("/auction/edit/{id}", name="auction_edit")
     *
     * @param Auction $auction
     * @param Request $request
     * @return Response
     */
    public function editAction(Auction $auction, Request $request){

        $form = $this->createForm(AuctionType::class, $auction);

        if($request->isMethod("post")){

            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush();

            return $this->redirectToRoute("auction_details", ["id"=>$auction->getId()]);
        }


        return $this->render("Auction/edit.html.twig", ["form"=>$form->createView()]);
    }

    /**
     * @Route("/auction/delete/{id}", name="auction_delete", methods={"DELETE"})
     *
     * @param Auction $auction
     * @return Response
     */
    public function deleteAction(Auction $auction){

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($auction);
        $entityManager->flush();

        return $this->redirectToRoute("auction_index");
    }

    /**
     * @Route("/auction/finish/{id}" , name="auction_finish", methods={"POST"})
     *
     * @param Auction $auction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function finishAction(Auction $auction){

        $auction->setExpiresAt(new \DateTime())
                ->setStatus(Auction::STATUS_FINISHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush();

        return $this->redirectToRoute("auction_details", ["id"=>$auction->getId()]);
    }
}