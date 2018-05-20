<?php

namespace AppBundle\Controller;


use AppBundle\Form\BidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Auction;


class AuctionController extends Controller
{
    /**
     * @Route("/home" , name="auction_index")
     *
     * @return Response
     */
    public function indexAction(){

        $entityManager = $this->getDoctrine()->getManager();

        $auctions = $entityManager->getRepository(Auction::class)->findBy(["status" => Auction::STATUS_ACTIVE]);

       return $this->render("Auction/index.html.twig",["auctions" => $auctions]);
    }

    /**
     * @Route("/auction/details/{id}", name="auction_details")
     *
     * @param Auction $auction
     * @return Response
     */
    public function detailsAction(Auction $auction){

        if($auction->getStatus() === Auction::STATUS_FINISHED){
            return $this->render("Auction/finished.html.twig", ["details"=>$auction]);
        }

        $buyForm = $this->createFormBuilder()
                        ->setAction($this->generateUrl("offer_buy", ["id" => $auction->getId()]))
                        ->setMethod(Request::METHOD_POST)
                        ->add("submit", SubmitType::class, ["label" => "Kup Teraz"])
                        ->getForm();

        $bidForm = $this->createForm(
                            BidType::class,
                            null,
                            ["action" => $this->generateUrl("offer_bid", ["id" => $auction->getId()])]
                        );

        return $this->render(
            "Auction/details.html.twig",
            [
                "details" => $auction,
                "buyForm" => $buyForm->createView(),
                "bidForm" => $bidForm->createView()
            ]
        );
    }
}