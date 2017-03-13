<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Form\MetiersType;
use AppBundle\Entity\Metiers;

/**
 * Metier controller.
 */
class MetiersController extends Controller
{
    /**
     * Lists all metier entities.
     *
     * @Rest\View()
     * @Rest\Get("/metiers")
     * @Security("has_role(2) || has_role(3)")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $metiers = $em->getRepository('AppBundle:Metiers')->findAll();

        return $metiers;
    }

    /**
     * Creates a new metier entity.
     *
     * @Rest\Get("/test")
     */
    public function testAction(Request $request)
    {
        return "";
    }
    /**
     * Creates a new metier entity.
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/metiers/new")
     * @Security("has_role(2) || has_role(3)")
     */
    public function newAction(Request $request)
    {
        $metiers = new Metiers();

        // $photo = $request->get('photo');
        // $photo->move($path);

        $metiers->setTitre($request->get('titre'))
        // ->setPhotoPath($path."/".?)
        ->setDefinition($request->get('definition'));

        if ($metiers->getTitre() == null || $metiers->getdefinition() == null || $metiers->getPhotoPath() == null) {
            return new JsonResponse(["message_err" => "Tous les champs ne sont pas remplis !"]);
        }

        $path = realpath($this->get('kernel')->getRootDir() . '/../web/pictures');
        
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($metiers);
        $em->flush();

        return $metiers;
    }

    /**
     * Finds and displays a metier entity.
     *
     * @Rest\View()
     * @Rest\Get("/metiers/{id}")
     * @Security("has_role(2) || has_role(3)")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $metiers = $em->getRepository('AppBundle:Metiers')->find($id);

        return $metiers;
    }

    /**
     * @Rest\View()
     * @Rest\Patch("/metiers/{id}")
     * @Security("has_role(2) || has_role(3)")
     */
    public function patchMetiersAction(Request $request)
    {
        return $this->updateMetiers($request);
    }

    public function updateMetiers(Request $request)
    {
        $metiers = $this->get('doctrine.orm.entity_manager')
        ->getRepository('AppBundle:Metiers')
        ->find($request->get('id'));

        if (empty($metiers)) {
            return new JsonResponse(['message_err' => "Le métier est introuvable !"], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(MetiersType::class, $metiers);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($metiers);
            $em->flush();
            return $metiers;
        } else {
            return $form;
        }
    }

    /**
     * Deletes a metier entity.
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/metiers/{id}")
     * @Security("has_role(2) || has_role(3)")
     */
    public function deleteAction(Request $request, Metiers $metier)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $metiers = $em->getRepository('AppBundle:Metiers')
        ->find($request->get('id'));

        if ($metiers) {
            $em->remove($metiers);
            $em->flush();
            return new JsonResponse(["message" => "Le métier a bien été supprimé !"]);
        }
        else
            return new JsonResponse(["message_err" => "Le métier est introuvable !"]);
    }
}
