<?php

namespace SV\AlertImprovements\XF\Pub\Controller;

use SV\AlertImprovements\XF\Repository\UserAlert;
use XF\Mvc\Entity\AbstractCollection;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;
use \XF\Entity\Thread as ThreadEntity;

/**
 * Class Thread
 *
 * @package SV\AlertImprovements\XF\Pub\Controller
 */
class Thread extends XFCP_Thread
{
    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|View
     * @throws \XF\Db\Exception
     */
    public function actionIndex(ParameterBag $params)
    {
        $reply = parent::actionIndex($params);

        if ($reply instanceof View && ($posts = $reply->getParam('posts')))
        {
            $visitor = \XF::visitor();

            if ($visitor->user_id && $visitor->alerts_unread)
            {
                $contentIds = $posts->keys();
                $contentType = 'post';

                /** @var UserAlert $alertRepo */
                $alertRepo = $this->repository('XF:UserAlert');
                $alertRepo->markAlertsReadForContentIds($contentType, $contentIds, null, 2010000);
            }
        }

        return $reply;
    }

    /**
     * @param ThreadEntity $thread
     * @param int $lastDate
     *
     * @return View
     * @throws \XF\Db\Exception
     */
    protected function getNewPostsReply(ThreadEntity $thread, $lastDate)
    {
        $reply = parent::getNewPostsReply($thread, $lastDate);

        if ($reply instanceof View && ($posts = $reply->getParam('posts')))
        {
            $visitor = \XF::visitor();

            if ($visitor->user_id && $visitor->alerts_unread)
            {
                $contentIds = $posts->keys();
                $contentType = 'post';

                /** @var UserAlert $alertRepo */
                $alertRepo = $this->repository('XF:UserAlert');
                $alertRepo->markAlertsReadForContentIds($contentType, $contentIds);
            }
        }

        return $reply;
    }

    /**
     * @param ThreadEntity $thread
     * @param int $lastDate
     * @param int          $limit
     *
     * @return array
     * @throws \XF\Db\Exception
     */
    protected function _getNextLivePosts(ThreadEntity $thread, $lastDate, $limit = 3)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        /**
         * @var AbstractCollection $contents
         * @var int                $lastDate
         */
        list ($contents, $lastDate) = parent::_getNextLivePosts($thread, $lastDate, $limit);

        $contentIds = $contents->keys();
        $contentType = 'post';

        /** @var UserAlert $alertRepo */
        $alertRepo = $this->repository('XF:UserAlert');
        $alertRepo->markAlertsReadForContentIds($contentType, $contentIds);

        return [$contents, $lastDate];
    }
}
