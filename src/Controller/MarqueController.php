<?php

namespace App\Controller;

use App\Entity\Marque;
use App\Form\MarqueType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarqueController extends AbstractController
{
    #[Route('/', name: 'app_marque')]
    public function index(EntityManagerInterface $em, Request $r): Response
    {
        $marque = new Marque();
        $form = $this->createForm(MarqueType::class, $marque);

        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get('logo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($logo) {
                $newFilename = uniqid() . '.' . $logo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $logo->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $marque->setLogo($newFilename);
            }

            $em->persist($marque);
            $em->flush();

            $this->addFlash('success', 'Marque ajoutée');
        }

        $marques = $em->getRepository(Marque::class)->findAll();

        return $this->render('marque/index.html.twig', [
            'marques' => $marques,
            'ajout' => $form->createView()
        ]);
    }

    #[Route('/marque/{id}', name: 'marque_edit')]
    public function edit(Marque $marque = null, Request $r, EntityManagerInterface $em)
    {
        if ($marque == null) {
            $this->addFlash('error', 'Marque inconnue');
            return $this->redirectToRoute('app_marque');
        }

        $form = $this->createForm(MarqueType::class, $marque);

        $form->handleRequest($r);
        if ($form->isSubmitted() && $form->isValid()) {
            $logo = $form->get('logo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($logo) {
                $newFilename = uniqid() . '.' . $logo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $logo->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );

                    // Sert à supprimer l'ancienne image en cas de mise à jour
                    if ($marque->getLogo() != null) {
                        unlink($this->getParameter('upload_directory') . '/' . $marque->getLogo());
                    }
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $marque->setLogo($newFilename);
            }

            $em->persist($marque);
            $em->flush();

            $this->addFlash('success', 'Marque ajoutée');
        }

        return $this->render('marque/show.html.twig', [
            'marque' => $marque,
            'edit' => $form->createView()
        ]);
    }

    #[Route('/marque/{id}/delete', name: 'marque_delete')]
    public function delete(Marque $marque = null, EntityManagerInterface $em)
    {
        if ($marque == null) {
            $this->addFlash('error', 'Marque inconnue');
            return $this->redirectToRoute('app_marque');
        }

        $em->remove($marque);
        $em->flush();

        $this->addFlash('warning', 'Marque supprimée');
        return $this->redirectToRoute('app_marque');
    }
}
