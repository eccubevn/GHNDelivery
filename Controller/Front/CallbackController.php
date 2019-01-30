<?php
/**
 * Author: lqdung1992@gmail.com
 * Date: 1/29/2019
 * Time: 1:38 PM
 */

namespace Plugin\GHNDelivery\Controller\Front;

use Eccube\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CallbackController extends AbstractController
{
    /**
     * @param Request $request
     * @Route(name="ghn_callback", path="/receive")
     */
    public function index(Request $request)
    {
    }
}