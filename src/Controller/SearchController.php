<?php

namespace App\Controller;

use \Datetime;
use \DateInterval;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Tarif;
use App\Entity\Compte;
use App\Entity\Entene;
use DateTimeImmutable;
use App\Entity\Chambre;
use App\Entity\Allocation;
use App\Entity\Typechambre;
use App\Entity\SortieFinanciere;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends DefaultController
{



    /**
     * @Route("/search/client", name="search-client", methods={"GET"})
     */
    public function searchClient(Request $request)
    {
        $term = $request->get('q');
        $data = $this->em->getRepository(User::class)->searchClient($term);
        return $this->json(['items' => $data]);
    }

}
