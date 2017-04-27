<?php
/**
 * Created by PhpStorm.
 * User: ben
 * Date: 19/03/2017
 * Time: 18:07
 */

namespace AppBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('resume', array($this, 'resumeFilter')),
        );
    }

    function resumeFilter($string, $wordsreturned = 100)
    {
        //On supprime les balises html de la chaine
        $string = strip_tags($string);
        //On crée un tableau avec tous les mots de la chaine
        $array = explode(" ", $string);
        //Si la longeur du tableau est inférieure au nombre de mots maxi on ne fait rien
        if (count($array) <= $wordsreturned) {
            $retval = $string;
        } // Sinon on garde que le nombre de mots désirés.
        else {
            //la fonction array_splice permet de ne garder que les X premières valeurs du tableau
            array_splice($array, $wordsreturned);
            //On crée une chaine avec toutes les valeurs du tableau séparées par un espace
            $retval = implode(" ", $array) . " ...";
        }

        return $retval;
    }
    public function getName()
    {
        return 'AppExtension';
    }

}