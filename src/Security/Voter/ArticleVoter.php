<?php

namespace App\Security\Voter;

use App\Entity\Article;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    const ARTICLE_READ       = 'article_read';
    const ARTICLE_EDIT       = 'article_edit';
    const ARTICLE_DEACTIVATE = 'article_deactivate';
    const ARTICLE_REACTIVATE = 'article_reactivate';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array(
            $attribute,
            [
                self::ARTICLE_READ,
                self::ARTICLE_EDIT,
                self::ARTICLE_DEACTIVATE,
                self::ARTICLE_REACTIVATE
            ]
        )
            && $subject instanceof \App\Entity\Article;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // support() method has ensured that $subject is an Article object
        /** 
         * @var Article $article 
         * */
        $article = $subject;

        // Check conditions and return boolean
        switch ($attribute) {
            case self::ARTICLE_READ:
                return $this->canRead($article, $user);
                break;
            case self::ARTICLE_EDIT:
                return $this->canEdit($article, $user);
                break;
            case self::ARTICLE_DEACTIVATE:
                return $this->canDeactivate($article, $user);
                break;
            case self::ARTICLE_REACTIVATE:
                return $this->canReactivate($article, $user);
                break;
        }
        return false;
    }

    /**
     * @param Article $article the subject of the voter
     * @param User $user the user requesting action on the subject
     * @return bool true if the user can read the article, false otherwise
     */
    private function canRead(Article $article, User $user)
    {
        return ($article->getAuthor() === $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    /**
     * @param Article $article the subject of the voter
     * @param User $user the user requesting action on the subject
     * @return bool true if the user can edit the article, false otherwise
     */
    private function canEdit(Article $article, User $user)
    {
        return (($article->getAuthor() === $user && $article->getStatus() !== 2) || $this->security->isGranted('ROLE_ADMIN'));
    }

    /**
     * @param Article $article the subject of the voter
     * @param User $user the user requesting action on the subject
     * @return bool true if the user can deactivate the article, false otherwise
     */
    private function canDeactivate(Article $article, User $user)
    {
        return ($article->getAuthor() === $user || $this->security->isGranted('ROLE_ADMIN'));
    }

    /**
     * @param Article $article the subject of the voter
     * @param User $user the user requesting action on the subject
     * @return bool true if the user can reactivate the article, false otherwise
     */
    private function canReactivate(Article $article, User $user)
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
