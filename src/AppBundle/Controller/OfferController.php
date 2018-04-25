<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Auction;
use AppBundle\Entity\Offer;
use AppBundle\Form\BidType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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

        $this->addFlash("success", "Kupiłeś przedmiot: {$auction->getTitle()} Za kwotę: {$offer->getPrice()}");

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }

    /**
     * @Route("/auction/bid/{id}", name="offer_bid", methods={"POST"})
     *
     * @param Request $request
     * @param Auction $auction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function bidAction(Request $request, Auction $auction){

        $offer = new Offer();
        $bidForm = $this->createForm(BidType::class, $offer);

        $bidForm->handleRequest($request);

        if($bidForm->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $lastOffer = $entityManager->getRepository(Offer::class)
                ->findOneBy(["auction" => $auction], ["createdAt" => "DESC"]);

            if(isset($lastOffer)){
                if ($offer->getPrice() <= $lastOffer->getPrice()) {
                    $this->addFlash("error", "Twoja oferta nie może być niższa niż {$lastOffer->getPrice()} zł");
                    return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
                }
            }
            elseif ($offer->getPrice() < $auction->getStartingPrice()) {

                $this->addFlash("error", "Nie można złożyć oferty mniejszej niż cena wywoławcza");
                return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
            }

            $offer
                ->setType(Offer::TYPE_AUCTION)
                ->setAuction($auction);


            $entityManager->persist($offer);
            $entityManager->flush();

            $this->addFlash("success", "Złożyłeś ofertę na przedmiot: {$auction->getTitle()} W wysokości: {$offer->getPrice()}");
        }
        else{
            $this->addFlash("error", "Nie można złożyć oferty");
        }

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
}