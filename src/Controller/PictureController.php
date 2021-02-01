<?php

namespace App\Controller;

use App\Form\SearchPictureType;
use App\Repository\PictureRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PictureController extends AbstractController
{
    /**
     * Affiche la liste des photos et le formulaire de recherche
     * @Route("/", name="picture_home")
     */
    public function home(PictureRepository $pictureRepository, Request $request): Response
    {
        //crée une instance du formulaire de recherche (il n'est pas associé à une entité)
        $searchForm = $this->createForm(SearchPictureType::class);

        //récupère les données soumises dans la requête
        $searchForm->handleRequest($request);

        //les données du form sont là (s'il a été soumis)
        $data = $searchForm->getData();
        dump($data);

        //récupère les photos (limit à 30 ici)
        $pictures = $pictureRepository->findBy([], [], 30);

        return $this->render('picture/home.html.twig', [
            'pictures' => $pictures,
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * Affiche le détail d'une photo
     * @Route("/details/{id}", name="picture_detail")
     */
    public function detail(int $id, PictureRepository $pictureRepository): Response
    {
        //récupère la photo dont l'id est dans l'URL
        $picture = $pictureRepository->find($id);

        return $this->render('picture/detail.html.twig', [
            'picture' => $picture
        ]);
    }

    /**
     * Affiche les photos associées à un tag
     * @Route("/tag/{id}", name="pictures_by_tag")
     */
    public function picturesByTag(int $id, TagRepository $tagRepository): Response
    {
        //récupère le tag (il contient toutes les photos associées !)
        $tag = $tagRepository->find($id);

        return $this->render('picture/pictures_by_tag.html.twig', [
            'tag' => $tag
        ]);
    }
}
