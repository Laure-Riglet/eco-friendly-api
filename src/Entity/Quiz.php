<?php

namespace App\Entity;

use App\Repository\QuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=QuizRepository::class)
 */
class Quiz
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"quizzes"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @assert\NotBlank(groups={"create"})
     * @assert\Length(min=3, max=255, groups={"create"})
     * @Groups({"quizzes"})
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity=Article::class, inversedBy="quizzes")
     * @ORM\JoinColumn(nullable=false)
     * @assert\NotBlank
     * @Groups({"quizzes"})
     */
    private $article;

    /**
     * @ORM\OneToMany(targetEntity=Answer::class, mappedBy="quiz", orphanRemoval=true, cascade={"persist"})
     * @assert\NotBlank(groups={"create"})
     * @assert\Count(min=4, max=4)
     * @Groups({"quizzes"})
     */
    private $answers;

    /**
     * @ORM\Column(type="integer")
     * @assert\NotBlank(groups={"create"})
     * @assert\Choice(choices={0, 1, 2}, groups={"create"})
     */
    private $status;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Groups({"quizzes"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"quizzes"})
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuiz($this);
        }

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuiz() === $this) {
                $answer->setQuiz(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
