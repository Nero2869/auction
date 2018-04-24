<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Auction;
use AppBundle\Entity\Offer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    /**
     * @Route("/auction/buy/{id}", name="offer_buy", methods={"post"})
     *
     * @param Auction $auction
     * @return Response
     */
    public function buyAction(Auction $auction){

        $offer = new Offer();

        $offer
                ->setAuction($auction)
                ->setPrice($auction->getPrice())
                ->setType(Offer::TYPE_BUY);

        $auction
                ->setStatus(Auction::STATUS_FINISHED)
                ->setExpiresAt(new \DateTime);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($offer);
        $entityManager->persist($auction);
        $entityManager->flush();

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
}