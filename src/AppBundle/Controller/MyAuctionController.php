<?php


namespace AppBundle\Controller;


use AppBundle\Entity\Auction;
use AppBundle\Form\AuctionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MyAuctionController extends Controller
{
    /**
     * @Route("/my", name="my_auction_index")
     *
     * @return Response
     */
    public function indexAction(){

        $this->denyAccessUnlessGranted("ROLE_USER");

        $entityManager = $this->getDoctrine()->getManager();

        $auctions = $entityManager
                                ->getRepository(Auction::class)
                                ->findBy(["owner" =>$this->getUser()]);

        return $this->render("MyAuction/index.html.twig", [
            "auctions" => $auctions
        ]);
    }

    /**
     * @Route("/my/auction/details/{id}", name="my_auction_details")
     *
     * @param Auction $auction
     * @return Response
     */
    public function detailsAction(Auction $auction){

        $this->denyAccessUnlessGranted("ROLE_USER");

        if($auction->getStatus() === Auction::STATUS_FINISHED){
            return $this->render("MyAuction/finished.html.twig", ["details"=>$auction]);
        }

        $formDelete = $this->createFormBuilder()
            ->add("submit", SubmitType::class, ["label" => "Usuń"])
            ->setMethod(Request::METHOD_DELETE)
            ->setAction($this->generateUrl("my_auction_delete", ["id" => $auction->getId()]))
            ->getForm();

        $finishForm = $this->createFormBuilder()
            ->setMethod(Request::METHOD_POST)
            ->setAction($this->generateUrl("my_auction_finish", ["id"=>$auction->getId()]))
            ->add("submit", SubmitType::class, ["label" => "Zakończ"])
            ->getForm();


        return $this->render(
            "MyAuction/details.html.twig",
            [
                "details" => $auction,
                "deleteForm" => $formDelete->createView(),
                "finishForm" => $finishForm->createView()
            ]
        );
    }

    /**
     * @Route("/my/auction/add", name="my_auction_add")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addAction(Request $request){

        $this->denyAccessUnlessGranted("ROLE_USER");

        $auction = new Auction();

        $form = $this->createForm(AuctionType::class, $auction);

        if($request->isMethod("post")){
            $form->handleRequest($request);

            if($auction->getStartingPrice() >= $auction->getPrice()){
                $form->get("startingPrice")->addError(new FormError("Cena wywoławcza nie może być wyższa od ceny kup teraz"));
            }

            if($form->isValid()){
                $auction->setStatus(Auction::STATUS_ACTIVE)
                    ->setOwner($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($auction);
                $entityManager->flush();

                $this->addFlash("success", "Aukcja {$auction->getTitle()} została pomyślnie dodana.");

                return $this->redirectToRoute("my_auction_details", ["id" => $auction->getId()]);
            }
            else{
                $this->addFlash("error", "Nie udało się dodać aukcji.");
            }
        }
        return $this->render("MyAuction/add.html.twig", ["form"=>$form->createView()]);
    }

    /**
     * @Route("/my/auction/edit/{id}", name="my_auction_edit")
     *
     * @param Auction $auction
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Auction $auction, Request $request){

        $this->denyAccessUnlessGranted("ROLE_USER");

        if($this->getUser() !== $auction->getOwner()){
            throw new AccessDeniedException();
        }

        $form = $this->createForm(AuctionType::class, $auction);

        if($request->isMethod("post")){

            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush();

            $this->addFlash("success","Aukcja {$auction->getTitle()} została pomyślnie zmodyfikowana.");

            return $this->redirectToRoute("my_auction_details", ["id"=>$auction->getId()]);
        }


        return $this->render("MyAuction/edit.html.twig", ["form"=>$form->createView()]);
    }

    /**
     * @Route("/my/auction/delete/{id}", name="my_auction_delete", methods={"DELETE"})
     *
     * @param Auction $auction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Auction $auction){

        $this->denyAccessUnlessGranted("ROLE_USER");

        if($this->getUser() !== $auction->getOwner()){
            throw new AccessDeniedException();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($auction);
        $entityManager->flush();

        $this->addFlash("success", "Aukcja {$auction->getTitle()} została usunięta.");

        return $this->redirectToRoute("my_auction_index");
    }

    /**
     * @Route("/my/auction/finish/{id}", name="my_auction_finish", methods={"POST"})
     *
     * @param Auction $auction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function finishAction(Auction $auction){

        $this->denyAccessUnlessGranted("ROLE_USER");

        if($this->getUser() !== $auction->getOwner()){
            throw new AccessDeniedException();
        }

        $auction->setExpiresAt(new \DateTime())
            ->setStatus(Auction::STATUS_FINISHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush();

        $this->addFlash("success", "Aukcja {$auction->getTitle()} została zakończona.");

        return $this->redirectToRoute("my_auction_details", ["id"=>$auction->getId()]);
    }
}