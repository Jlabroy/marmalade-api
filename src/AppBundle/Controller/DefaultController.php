<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/version", name="version")
     */
    public function versionAction()
    {
        return new JsonResponse('1.01');
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $pages = $this->getDoctrine()
            ->getRepository('AppBundle:Page')
            ->findByUserId($request->get('user_id'));

        $return = [];
        foreach ($pages as $k => $page) {
            $return[] = [
                'user_id' => $page->getUserId(),
                'url' => $page->getUrl(),
                'content' => $page->getContent()
            ];
        }

        return new JsonResponse($return);
    }

    /**
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request)
    {
        if ($request->get('query') == '') {
            return new JsonResponse([]);
        }

        $query = explode(' ', $request->get('query'));

        $sql = sprintf("
              SELECT 
                * 
              FROM (
                SELECT 
                  url,
                  title,
                  meta_description,
                  content,
                  to_tsvector(url) || 
                  to_tsvector(title) || 
                  to_tsvector(meta_description) || 
                  to_tsvector(content) as document 
                FROM pages
              ) p_search 
              WHERE p_search.document @@ to_tsquery('%s:*');",
            implode('&', $query)
        );

        $em = $this->getDoctrine()->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        return new JsonResponse($results);
    }

    /**
     * @Route("/user", name="user")
     */
    public function userAction(Request $request)
    {
        if (empty($request->get('first_name')) ||
            empty($request->get('email')) ||
            empty($request->get('password'))
        ) {
            throw new \Exception('not all required params set.');
        }

        $user = new User([
            'first_name' => $request->get('first_name'),
            'email' => $request->get('email'),
            'password' => $request->get('password')
        ]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new JsonResponse($user->getId());
    }

    /**
     * @Route("/session", name="session")
     */
    public function sessionAction(Request $request)
    {
        if (empty($request->get('email')) ||
            empty($request->get('password'))
        ) {
            throw new \Exception('not all required params set.');
        }

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['email' => $request->get('email'), 'password' => $request->get('password')]);

        if (count($user) == 0) {
            throw new \Exception('no user found');
        }

        return new JsonResponse($user->getId());
    }

    /**
     * @Route("/page", name="page")
     */
    public function pageAction(Request $request)
    {
        $page = $request->get('url');
        $userId = $request->get('user_id');

        exec(sprintf('python /Users/James/SearchSaaS/python/UrlSearch.py %s %s', $page, $userId));

        return new JsonResponse(true);
    }
}
