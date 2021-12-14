<?php

namespace App\Controller;

use App\Entity\ResultJson;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\NameSms;

class DefaultController extends AbstractController
{
    private
        $user_id = 1;

    /**
     * @Route("/")
     *
     */
    public function index()
    {
        $inx = 'default/index.html.twig';
        return $this->render($inx, [
        ]);
    }

    /**
     * Заказ буквенного имени
     * @Route("/name-order")
     *
     */
    public function nameOrder(Request $request, NameSms $name_sms)
    {
        // Принимаем параметры с формы
        $params = $request->request->all();
        // Пробуем заказать имя
        if ($params) {
            $sms_name_status = $name_sms->nameOrder($this->user_id, $params['sms_name']);
        } else {
            $params['sms_name'] = '';
            $sms_name_status = new ResultJson(200, '');
        }
        return $this->render('default/name-order.html.twig', [
            'params' => $params,
            'sms_name_status' => $sms_name_status->status_msg,
        ]);
    }

    /**
     * Получим список буквенных имен клиента
     * @Route("/name-list")
     *
     */
    public function nameList(NameSms $name_sms)
    {
        // Получим список смс имен пользователя
        $name_list = $name_sms->nameList($this->user_id);
        return $this->render('default/name-list.html.twig', [
            'name_list' => $name_list,
        ]);
    }


}