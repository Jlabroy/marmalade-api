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

        $sql = sprintf("select 
            url, 
            title,
            meta_description,
            content,
            match(url, title, meta_description, content) against ('%s' in natural language mode) as score 
          from pages 
          order by score desc
          LIMIT 10;",
            $request->get('query')
        );

        $em = $this->getDoctrine()->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        foreach ($results as $k => $result) {
            if ($result['score'] == 0) {
                unset($results[$k]);
                continue;
            }

            $results[$k]['title'] = substr($result['title'], 1, strlen($result['title']) - 2);
            $results[$k]['url'] = substr($result['url'], 1, strlen($result['url']) - 2);
            $results[$k]['meta_description'] = substr($result['meta_description'], 1, strlen($result['meta_description']) - 2);
            $results[$k]['content'] = substr($result['content'], 1, strlen($result['content']) - 2);
            $results[$k]['score'] = $result['score'];

        }


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
