<?php

namespace History\Entities\Synchronizers;

use History\Entities\Models\AbstractModel;
use History\Entities\Models\Threads\Comment;

class CommentSynchronizer extends AbstractSynchronizer
{
    /**
     * @var string
     */
    protected $entity = Comment::class;

    /**
     * @var array
     */
    protected $protected = [
        'comment_id',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getMatchers()
    {
        return [
            [
                'xref' => $this->informations->get('xref'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSynchronizedFields(AbstractModel $entity)
    {
        return [
            'xref' => $this->informations->get('xref'),
            'reference' => $this->informations->get('reference'),
            'name' => $this->informations->get('subject'),
            'contents' => $this->informations->get('contents'),
            'thread_id' => $this->informations->get('thread_id'),
            'comment_id' => $this->informations->get('comment_id'),
            'user_id' => $this->informations->get('user_id'),
        ];
    }
}
