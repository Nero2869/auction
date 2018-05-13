<?php

namespace AppBundle\Twig;


use Twig_Extension;

class DateExtensions extends Twig_Extension
{
    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter("expireDate", [$this, "expireDate"])
        ];
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction("styleAuction", [$this, "styleAuction"])
        ];
    }

    /**
     * @param \DateTime $expiresAt
     * @return string
     */
    function expireDate(\DateTime $expiresAt){

        if($expiresAt > new \DateTime("+1 month")){
            return $expiresAt->format("Y-m-d H:i");
        }

        if($expiresAt < new \DateTime("+1 month") && $expiresAt > new \DateTime("+1 day")){
            return "za ". $expiresAt->diff(new \DateTime())->days . " dni";
        }

        if($expiresAt < new \DateTime("+1 day")){
            return "za " . $expiresAt->format("H") . "godz. " . $expiresAt->format("i") . " min.";
        }

    }

    /**
     * @param \DateTime $expiresAt
     * @return string
     */
    public function styleAuction(\DateTime $expiresAt){

        if($expiresAt < new \DateTime("+1 day")){
            return "panel-danger";
        }
        else{
            return "panel-default";
        }
    }
}