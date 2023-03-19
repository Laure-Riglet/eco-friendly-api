<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Form\AdviceType;
use App\Form\ContentListType;
use App\Repository\AdviceRepository;
use App\Service\SluggerService;
use DateTime;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AdviceController extends AbstractController
{
    /**
     * @Route("/conseils", name="bo_advices_list", methods={"GET", "POST"}, host="backoffice.eco-friendly.localhost")
     * @isGranted("ROLE_ADMIN"), message="Vous n'avez pas les droits pour accéder à cette page"
     */
    public function list(Request $request, AdviceRepository $adviceRepository): Response
    {
        $advices = $adviceRepository->findAllOrderByDate();

        $form = $this->createForm(ContentListType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advices = $adviceRepository->findAllWithFilter(
                $form->get('sortType')->getData() ?? 'created_at',
                $form->get('sortOrder')->getData() ?? 'DESC',
                $form->get('title')->getData(),
                $form->get('content')->getData(),
                $form->get('status')->getData(),
                $form->get('user')->getData(),
                $form->get('category')->getData(),
                DateTimeImmutable::createFromMutable($form->get('dateFrom')->getData() ?? new DateTime('2000-01-01')),
                DateTimeImmutable::createFromMutable($form->get('dateTo')->getData() ?? new DateTime('now'))
            );

            return $this->render('advice/list.html.twig', [
                'advices' => $advices,
                'form' => $form->createView()
            ]);
        }

        return $this->renderForm('advice/list.html.twig', [
            'advices' => $advices,
            'form' => $form
        ]);
    }

    /**
     * @Route("/conseils/{id}", name="bo_advices_show", requirements={"id":"\d+"}, methods={"GET"}, host="backoffice.eco-friendly.localhost")
     * @isGranted("ROLE_ADMIN"), message="Vous n'avez pas les droits pour accéder à cette page"
     */
    public function show(Advice $advice): Response
    {
        return $this->render('advice/show.html.twig', [
            'advice' => $advice,
        ]);
    }

    /**
     * @Route("/conseils/{id}/editer", name="bo_advices_edit", requirements={"id":"\d+"}, methods={"GET", "POST"}, host="backoffice.eco-friendly.localhost")
     * @isGranted("ROLE_ADMIN"), message="Vous n'avez pas les droits pour accéder à cette page"
     */
    public function edit(Request $request, SluggerService $slugger, Advice $advice, AdviceRepository $adviceRepository): Response
    {
        $form = $this->createForm(AdviceType::class, $advice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $advice->setSlug($slugger->slugify($advice->getTitle()));
            $advice->setUpdatedAt(new DateTimeImmutable());
            $adviceRepository->add($advice, true);

            $this->addFlash(
                'success',
                '"' . $advice->getTitle() . '" a bien été modifié.'
            );

            return $this->redirectToRoute('bo_advices_list', [], Response::HTTP_SEE_OTHER);
        }


        return $this->renderForm('advice/edit.html.twig', [
            'advice' => $advice,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/conseils/{id}/desactiver", name="bo_advices_deactivate", requirements={"id":"\d+"}, methods={"POST"}, host="backoffice.eco-friendly.localhost")
     * @isGranted("ROLE_ADMIN"), message="Vous n'avez pas les droits pour accéder à cette page"
     */
    public function deactivate(Advice $advice, AdviceRepository $adviceRepository, Request $request): Response
    {
        if ($this->isCsrfTokenValid('deactivate' . $advice->getId(), $request->request->get('_token'))) {
            $advice->setStatus(2);
            $advice->setUpdatedAt(new DateTimeImmutable());
            $adviceRepository->add($advice, true);
        }
        $this->addFlash(
            'danger',
            '"' . $advice->getTitle() . '" a bien été désactivé. '
        );
        return $this->redirectToRoute('bo_advices_list', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/conseils/{id}/reactiver", name="bo_advices_reactivate", requirements={"id":"\d+"}, methods={"POST"}, host="backoffice.eco-friendly.localhost")
     * @isGranted("ROLE_ADMIN"), message="Vous n'avez pas les droits pour accéder à cette page"
     */
    public function reactivate(Advice $advice, AdviceRepository $adviceRepository, Request $request): Response
    {
        if ($this->isCsrfTokenValid('reactivate' . $advice->getId(), $request->request->get('_token'))) {
            $advice->setStatus(1);
            $advice->setUpdatedAt(new DateTimeImmutable());
            $adviceRepository->add($advice, true);
        }
        $this->addFlash(
            'success',
            '"' . $advice->getTitle() . '" a bien été réactivé. '
        );
        return $this->redirectToRoute('bo_advices_list', [], Response::HTTP_SEE_OTHER);
    }
}
