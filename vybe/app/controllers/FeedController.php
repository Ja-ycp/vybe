<?php
declare(strict_types=1);

class FeedController
{
    private PDO $conn;
    private PostModel $postModel;
    private UserModel $userModel;
    private CommentModel $commentModel;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->postModel = new PostModel($db);
        $this->userModel = new UserModel($db);
        $this->commentModel = new CommentModel($db);
    }

    public function index(?int $id = null): void
    {
        $viewerId = (int) $_SESSION['user_id'];
        $search = trim($_GET['search'] ?? '');
        $posts = $this->attachComments($this->postModel->getFeed($viewerId));
        $searchResults = $search !== '' ? $this->userModel->search($search, null, 10) : [];
        $discoverUsers = $this->userModel->getRecentUsers($viewerId, 6);

        app_render('feed', [
            'title' => 'Newsfeed',
            'posts' => $posts,
            'search' => $search,
            'searchResults' => $searchResults,
            'discoverUsers' => $discoverUsers,
        ]);
    }

    private function attachComments(array $posts): array
    {
        $postIds = array_map(static fn(array $post): int => (int) $post['id'], $posts);
        $commentsByPost = $this->commentModel->getByPostIds($postIds, (int) $_SESSION['user_id']);

        foreach ($posts as &$post) {
            $post['comments'] = $commentsByPost[(int) $post['id']] ?? [];
        }
        unset($post);

        return $posts;
    }
}
?>

