<?php

namespace History\Http\Controllers;

use History\Entities\Models\Threads\Group;

class GroupsController extends AbstractController
{
    /**
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function index()
    {
        $groups = Group::all();

        return $this->render('groups/index.twig', [
            'groups' => $groups,
        ]);
    }

    /**
     * @param Group $group
     *
     * @return \Zend\Diactoros\Response\HtmlResponse
     */
    public function show(Group $group)
    {
        return $this->render('groups/show.twig', [
            'group' => $group,
        ]);
    }
}
