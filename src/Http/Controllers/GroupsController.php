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
        $groups = Group::with('threads')->get();
        $groups = $groups->sortByDesc(function (Group $group) {
           return $group->threads->count()
               ? $group->threads->first()->created_at->toDateTimeString()
               : null;
        });

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
