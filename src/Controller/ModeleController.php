<?php

namespace App\Controller;

use App\Entity\Modele;
use App\Form\ModeleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/modele')]
class ModeleController extends AbstractController
{
    #[Route('/', name: 'app_modele')]
    public function index(EntityManagerInterface $em, Request $r): Response
    {
        $modele = new Modele();
        $form = $this->createForm(ModeleType::class, $modele);

        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($modele);
            $em->flush();

            $this->addFlash('success', 'Modele ajouté');
        }

        $modeles = $em->getRepository(Modele::class)->findAll();

        return $this->render('modele/index.html.twig', [
            'modeles' => $modeles,
            'ajout' => $form->createView()
        ]);
    }

    #[Route('/{id}', name: 'modele_edit')]
    public function edit(Modele $modele = null, Request $r, EntityManagerInterface $em)
    {
        if ($modele == null) {
            $this->addFlash('error', 'Modele introuvable');
            return $this->redirectToRoute('app_modele');
        }

        $form = $this->createForm(ModeleType::class, $modele);

        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($modele);
            $em->flush();

            $this->addFlash('success', 'Modele modifié');
        }

        return $this->render('modele/show.html.twig', [
            'modele' => $modele,
            'edit' => $form->createView()
        ]);
    }

    #[Route('/{id}/delete', name: 'modele_delete')]
    public function delete(Modele $modele = null, EntityManagerInterface $em)
    {
        if ($modele == null) {
            $this->addFlash('error', 'Modele introuvable');
            return $this->redirectToRoute('app_modele');
        }

        $em->remove($modele);
        $em->flush();

        $this->addFlash('warning', 'Modele supprimé');
        return $this->redirectToRoute('app_modele');
    }
}
