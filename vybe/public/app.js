const UI_KEY = "vybe-ui-state-v1";

const savedUi = JSON.parse(localStorage.getItem(UI_KEY) || "{}");

const state = {
  bootstrap: null,
  activeView: "feed",
  activeFilter: "all",
  searchTerm: "",
  selectedMood: "Inspired",
  openComments: new Set(),
  connectedPeople: new Set(savedUi.connectedPeople || []),
  joinedCircles: new Set(savedUi.joinedCircles || []),
  statusTimer: null,
};

const elements = {
  authScreen: document.querySelector("#auth-screen"),
  appScreen: document.querySelector("#app-screen"),
  searchShell: document.querySelector("#search-shell"),
  guestActions: document.querySelector("#guest-actions"),
  sessionActions: document.querySelector("#session-actions"),
  globalSearch: document.querySelector("#global-search"),
  statusBanner: document.querySelector("#status-banner"),
  loginForm: document.querySelector("#login-form"),
  registerForm: document.querySelector("#register-form"),
  logoutButton: document.querySelector("#logout-button"),
  composerForm: document.querySelector("#composer-form"),
  composerText: document.querySelector("#composer-text"),
  composerAudience: document.querySelector("#composer-audience"),
  composerImage: document.querySelector("#composer-image"),
  composerImageName: document.querySelector("#composer-image-name"),
  memoryForm: document.querySelector("#memory-form"),
  feedList: document.querySelector("#feed-list"),
  circlesList: document.querySelector("#circles-list"),
  peopleList: document.querySelector("#people-list"),
  memoriesGrid: document.querySelector("#memories-grid"),
  profilePosts: document.querySelector("#profile-posts"),
  pulseList: document.querySelector("#pulse-list"),
  suggestedList: document.querySelector("#suggested-list"),
  currentUserName: document.querySelector("#current-user-name"),
  currentUserBio: document.querySelector("#current-user-bio"),
  currentUserAvatar: document.querySelector("#current-user-avatar"),
  composerName: document.querySelector("#composer-name"),
  composerAvatar: document.querySelector("#composer-avatar"),
  heroStatPosts: document.querySelector("#hero-stat-posts"),
  heroStatCircles: document.querySelector("#hero-stat-circles"),
  heroStatPeople: document.querySelector("#hero-stat-people"),
  profileName: document.querySelector("#profile-name"),
  profileRole: document.querySelector("#profile-role"),
  profileAvatar: document.querySelector("#profile-avatar"),
  profileForm: document.querySelector("#profile-form"),
  profileNameInput: document.querySelector("#profile-name-input"),
  profileBioInput: document.querySelector("#profile-bio-input"),
  viewButtons: document.querySelectorAll("[data-view-target]"),
  filterButtons: document.querySelectorAll(".filter-chip"),
  moodButtons: document.querySelectorAll(".mood-chip"),
  appViews: document.querySelectorAll(".app-view"),
  jumpButtons: document.querySelectorAll("[data-jump='composer']"),
  scrollButtons: document.querySelectorAll("[data-scroll-target]"),
  mobileDock: document.querySelector(".mobile-dock"),
};

function saveUiState() {
  localStorage.setItem(
    UI_KEY,
    JSON.stringify({
      connectedPeople: [...state.connectedPeople],
      joinedCircles: [...state.joinedCircles],
    }),
  );
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function formatParagraph(text) {
  return escapeHtml(text).replaceAll("\n", "<br />");
}

function initials(name) {
  return String(name ?? "")
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? "")
    .join("");
}

function formatRelativeTime(timestamp) {
  const diff = Date.now() - Number(timestamp);
  const minute = 1000 * 60;
  const hour = minute * 60;
  const day = hour * 24;

  if (diff < minute) return "Just now";
  if (diff < hour) return `${Math.floor(diff / minute)}m ago`;
  if (diff < day) return `${Math.floor(diff / hour)}h ago`;
  if (diff < day * 7) return `${Math.floor(diff / day)}d ago`;

  return new Date(timestamp).toLocaleDateString(undefined, {
    month: "short",
    day: "numeric",
  });
}

async function api(url, options = {}) {
  const isFormData = options.body instanceof FormData;
  const response = await fetch(url, {
    credentials: "same-origin",
    ...options,
    headers: {
      Accept: "application/json",
      ...(isFormData ? {} : { "Content-Type": "application/json" }),
      ...(options.headers || {}),
    },
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(data.error || "Request failed.");
  }

  return data;
}

function showStatus(message, type = "success") {
  clearTimeout(state.statusTimer);
  elements.statusBanner.textContent = message;
  elements.statusBanner.classList.remove("is-hidden", "is-error", "is-success");
  elements.statusBanner.classList.add(type === "error" ? "is-error" : "is-success");

  state.statusTimer = window.setTimeout(() => {
    elements.statusBanner.classList.add("is-hidden");
  }, 3400);
}

function requireUserAction() {
  elements.authScreen.scrollIntoView({ behavior: "smooth", block: "start" });
  showStatus("Log in or create an account to use Vybe.", "error");
}

function setView(view) {
  if (!state.bootstrap?.currentUser) {
    requireUserAction();
    return;
  }

  state.activeView = view;

  elements.appViews.forEach((section) => {
    const isMatch = section.dataset.view === view;
    section.hidden = !isMatch;
    section.classList.toggle("is-active", isMatch);
  });

  elements.viewButtons.forEach((button) => {
    button.classList.toggle("is-active", button.dataset.viewTarget === view);
  });
}

function applyAuthState() {
  const isLoggedIn = Boolean(state.bootstrap?.currentUser);

  elements.authScreen.classList.toggle("is-hidden", isLoggedIn);
  elements.appScreen.classList.toggle("is-hidden", !isLoggedIn);
  elements.searchShell.classList.toggle("is-hidden", !isLoggedIn);
  elements.guestActions.classList.toggle("is-hidden", isLoggedIn);
  elements.sessionActions.classList.toggle("is-hidden", !isLoggedIn);
  elements.mobileDock.classList.toggle("is-hidden", !isLoggedIn);
}

function updateUserPanels() {
  const user = state.bootstrap?.currentUser;
  if (!user) return;

  elements.currentUserName.textContent = user.name;
  elements.currentUserBio.textContent = user.bio;
  elements.currentUserAvatar.textContent = initials(user.name);
  elements.composerName.textContent = user.name;
  elements.composerAvatar.textContent = initials(user.name);
  elements.profileName.textContent = user.name;
  elements.profileRole.textContent = user.role;
  elements.profileAvatar.textContent = initials(user.name);
  elements.profileNameInput.value = user.name;
  elements.profileBioInput.value = user.bio;
}

function renderStats() {
  const stats = state.bootstrap?.stats;
  if (!stats) return;

  elements.heroStatPosts.textContent = String(stats.postCount);
  elements.heroStatCircles.textContent = String(stats.circleCount);
  elements.heroStatPeople.textContent = String(stats.peopleCount);
}

function postMatches(post) {
  if (!state.searchTerm) return true;
  const haystack = [
    post.author?.name,
    post.author?.username,
    post.author?.role,
    post.content,
    post.audience,
    post.mood,
  ]
    .join(" ")
    .toLowerCase();

  return haystack.includes(state.searchTerm);
}

function personMatches(person) {
  if (!state.searchTerm) return true;
  return `${person.name} ${person.username} ${person.role} ${person.bio}`
    .toLowerCase()
    .includes(state.searchTerm);
}

function circleMatches(circle) {
  if (!state.searchTerm) return true;
  return `${circle.name} ${circle.label} ${circle.description}`
    .toLowerCase()
    .includes(state.searchTerm);
}

function memoryMatches(memory) {
  if (!state.searchTerm) return true;
  return `${memory.type} ${memory.title} ${memory.author} ${memory.description}`
    .toLowerCase()
    .includes(state.searchTerm);
}

function renderEmpty(message, detail) {
  return `
    <article class="panel empty-state">
      <strong>${escapeHtml(message)}</strong>
      <p class="muted">${escapeHtml(detail)}</p>
    </article>
  `;
}

function renderFeedCard(post) {
  const commentsOpen = state.openComments.has(post.id);

  return `
    <article class="panel feed-card" data-group="${escapeHtml(post.group)}">
      <div class="feed-head">
        <div class="feed-meta">
          <div class="avatar">${escapeHtml(initials(post.author?.name || "Vybe"))}</div>
          <div>
            <strong>${escapeHtml(post.author?.name || "Unknown author")}</strong>
            <div class="comment-meta">
              ${escapeHtml(post.author?.role || "Member")} - <span class="handle">@${escapeHtml(post.author?.username || "user")}</span>
            </div>
          </div>
        </div>

        <div>
          <div class="meta-pill">${escapeHtml(post.audience)}</div>
          <div class="post-time">${escapeHtml(formatRelativeTime(post.createdAt))}</div>
        </div>
      </div>

      <p class="feed-content">${formatParagraph(post.content || "")}</p>
      ${post.imagePath ? `<img class="post-image" src="${escapeHtml(post.imagePath)}" alt="Post image" />` : ""}

      <div class="tag-row">
        <span class="tag">${escapeHtml(post.mood)}</span>
        <span class="tag">${escapeHtml(post.group)}</span>
      </div>

      <div class="card-actions">
        <button class="action-button ${post.likedByViewer ? "is-active" : ""}" data-action="like" data-post-id="${escapeHtml(post.id)}" type="button">
          Like ${escapeHtml(String(post.likeCount))}
        </button>
        <button class="action-button ${commentsOpen ? "is-active" : ""}" data-action="toggle-comments" data-post-id="${escapeHtml(post.id)}" type="button">
          Comments ${escapeHtml(String(post.comments.length))}
        </button>
        ${
          post.canEdit
            ? `<button class="ghost-button" data-action="delete-post" data-post-id="${escapeHtml(post.id)}" type="button">Delete</button>`
            : ""
        }
      </div>

      ${
        commentsOpen
          ? `
            <div class="comments-wrap">
              ${
                post.comments.length
                  ? post.comments
                      .map(
                        (comment) => `
                          <article class="comment-item">
                            <strong>${escapeHtml(comment.author?.name || "Unknown")}</strong>
                            <div class="comment-meta">@${escapeHtml(comment.author?.username || "user")} - ${escapeHtml(formatRelativeTime(comment.createdAt))}</div>
                            <div class="comment-body">${escapeHtml(comment.text)}</div>
                          </article>
                        `,
                      )
                      .join("")
                  : `<p class="muted">No comments yet. Start the conversation.</p>`
              }
              <form class="comment-form" data-post-id="${escapeHtml(post.id)}">
                <input type="text" name="comment" placeholder="Write a comment" maxlength="180" autocomplete="off" />
                <button class="button-primary" type="submit">Reply</button>
              </form>
            </div>
          `
          : ""
      }
    </article>
  `;
}

function renderFeed() {
  const posts = (state.bootstrap?.posts || [])
    .filter((post) => state.activeFilter === "all" || post.group === state.activeFilter)
    .filter(postMatches);

  elements.feedList.innerHTML = posts.length
    ? posts.map(renderFeedCard).join("")
    : renderEmpty(
        "No posts yet.",
        "Create the first post on Vybe or adjust your search and filters.",
      );
}

function renderCircles() {
  const circles = (state.bootstrap?.circles || []).filter(circleMatches);

  elements.circlesList.innerHTML = circles.length
    ? circles
        .map((circle) => {
          const joined = state.joinedCircles.has(circle.id);
          return `
            <article class="circle-card">
              <div class="circle-row">
                <div>
                  <span>${escapeHtml(circle.label)}</span>
                  <h4>${escapeHtml(circle.name)}</h4>
                </div>
                <button class="circle-button ${joined ? "is-active" : ""}" data-action="toggle-circle" data-circle-id="${escapeHtml(circle.id)}" type="button">
                  ${joined ? "Joined" : "Join"}
                </button>
              </div>
              <p class="circle-text">${escapeHtml(circle.description)}</p>
              <div class="tag-row">
                <span class="tag">${escapeHtml(String(circle.members))} members</span>
              </div>
            </article>
          `;
        })
        .join("")
    : renderEmpty("No circles found.", "Try a broader search term.");
}

function renderPeople() {
  const currentUserId = state.bootstrap?.currentUser?.id;
  const users = (state.bootstrap?.users || [])
    .filter((person) => person.id !== currentUserId)
    .filter(personMatches);

  elements.peopleList.innerHTML = users.length
    ? users
        .map((person) => {
          const connected = state.connectedPeople.has(person.id);
          return `
            <article class="person-card">
              <div class="person-row">
                <div class="user-chip">
                  <div class="avatar">${escapeHtml(initials(person.name))}</div>
                  <div>
                    <h4>${escapeHtml(person.name)}</h4>
                    <div class="comment-meta">@${escapeHtml(person.username)} - ${escapeHtml(person.role)}</div>
                  </div>
                </div>
                <button class="circle-button ${connected ? "is-active" : ""}" data-action="toggle-person" data-person-id="${escapeHtml(person.id)}" type="button">
                  ${connected ? "Connected" : "Connect"}
                </button>
              </div>
              <p class="person-text">${escapeHtml(person.bio)}</p>
            </article>
          `;
        })
        .join("")
    : renderEmpty(
        "No other people yet.",
        "Create more accounts to start building your Vybe community.",
      );
}

function renderMemories() {
  const memories = (state.bootstrap?.memories || []).filter(memoryMatches);

  elements.memoriesGrid.innerHTML = memories.length
    ? memories
        .map(
          (memory) => `
            <article class="memory-card" style="--memory-tint:${escapeHtml(memory.tint)}">
              <div class="memory-top">
                <span class="memory-type">${escapeHtml(memory.type)}</span>
                <div class="memory-badge">${escapeHtml(memory.badge)}</div>
              </div>
              <h4>${escapeHtml(memory.title)}</h4>
              <p>${escapeHtml(memory.description)}</p>
              <div class="memory-footer">
                <span class="comment-meta">by ${escapeHtml(memory.author)}</span>
                ${
                  memory.canEdit
                    ? `<button class="ghost-button" data-action="delete-memory" data-memory-id="${escapeHtml(memory.id)}" type="button">Delete</button>`
                    : `<span class="tag">Genuine memory</span>`
                }
              </div>
            </article>
          `,
        )
        .join("")
    : renderEmpty(
        "No memory boards yet.",
        "Create the first memory board for your community here.",
      );
}

function renderProfile() {
  const currentUserId = state.bootstrap?.currentUser?.id;
  const posts = (state.bootstrap?.posts || []).filter(
    (post) => post.author?.id === currentUserId && postMatches(post),
  );

  elements.profilePosts.innerHTML = posts.length
    ? posts.map(renderFeedCard).join("")
    : renderEmpty(
        "You have no posts in this view.",
        "Create a new post or clear the search to see more.",
      );
}

function renderPulse() {
  const posts = state.bootstrap?.posts || [];
  const circles = state.bootstrap?.circles || [];

  if (!posts.length) {
    elements.pulseList.innerHTML = `
      <article class="pulse-item">
        <strong>No activity yet</strong>
        <p>Create the first post to start the community pulse.</p>
      </article>
    `;
    return;
  }

  const byAudience = circles.map((circle) => ({
    title: `${circle.name} is active`,
    body: `${posts.filter((post) => post.audience === circle.name).length} recent moments are keeping this circle alive.`,
  }));

  elements.pulseList.innerHTML = byAudience
    .slice(0, 4)
    .map(
      (item) => `
        <article class="pulse-item">
          <strong>${escapeHtml(item.title)}</strong>
          <p>${escapeHtml(item.body)}</p>
        </article>
      `,
    )
    .join("");
}

function renderSuggested() {
  const currentUserId = state.bootstrap?.currentUser?.id;
  const users = (state.bootstrap?.users || []).filter((person) => person.id !== currentUserId);

  elements.suggestedList.innerHTML = users.length
    ? users
        .slice(0, 3)
        .map(
          (person) => `
            <article class="suggested-item">
              <strong>${escapeHtml(person.name)}</strong>
              <p>${escapeHtml(person.role)} - @${escapeHtml(person.username)}</p>
            </article>
          `,
        )
        .join("")
    : `
        <article class="suggested-item">
          <strong>No suggestions yet</strong>
          <p>Create another account and this area will start filling up.</p>
        </article>
      `;
}

function renderAll() {
  applyAuthState();

  if (!state.bootstrap?.currentUser) {
    return;
  }

  updateUserPanels();
  renderStats();
  renderFeed();
  renderCircles();
  renderPeople();
  renderMemories();
  renderProfile();
  renderPulse();
  renderSuggested();
}

function replacePost(updatedPost) {
  state.bootstrap.posts = state.bootstrap.posts.map((post) =>
    post.id === updatedPost.id ? updatedPost : post,
  );
}

async function loadBootstrap() {
  state.bootstrap = await api("/api/bootstrap", {
    method: "GET",
    headers: {},
  });
  renderAll();
}

function bindStaticEvents() {
  elements.viewButtons.forEach((button) => {
    button.addEventListener("click", () => {
      setView(button.dataset.viewTarget);
      if (state.bootstrap?.currentUser) {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    });
  });

  elements.filterButtons.forEach((button) => {
    button.addEventListener("click", () => {
      state.activeFilter = button.dataset.filter;
      elements.filterButtons.forEach((item) =>
        item.classList.toggle("is-active", item === button),
      );
      renderFeed();
    });
  });

  elements.moodButtons.forEach((button) => {
    button.addEventListener("click", () => {
      state.selectedMood = button.dataset.mood;
      elements.moodButtons.forEach((item) =>
        item.classList.toggle("is-selected", item === button),
      );
    });
  });

  elements.globalSearch.addEventListener("input", (event) => {
    state.searchTerm = event.target.value.trim().toLowerCase();
    if (state.bootstrap?.currentUser) {
      renderFeed();
      renderCircles();
      renderPeople();
      renderMemories();
      renderProfile();
    }
  });

  elements.scrollButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const target = document.getElementById(button.dataset.scrollTarget);
      target?.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  });

  elements.jumpButtons.forEach((button) => {
    button.addEventListener("click", () => {
      setView("feed");
      document.querySelector("#composer")?.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
      elements.composerText.focus();
    });
  });

  elements.composerImage.addEventListener("change", () => {
    const file = elements.composerImage.files?.[0];
    elements.composerImageName.textContent = file ? `Selected: ${file.name}` : "";
  });

  elements.loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(elements.loginForm);

    try {
      state.bootstrap = await api("/api/auth/login", {
        method: "POST",
        body: JSON.stringify({
          username: formData.get("username"),
          password: formData.get("password"),
        }),
      });
      state.searchTerm = "";
      elements.globalSearch.value = "";
      elements.loginForm.reset();
      renderAll();
      showStatus("Logged in successfully.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });

  elements.registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const formData = new FormData(elements.registerForm);

    try {
      state.bootstrap = await api("/api/auth/register", {
        method: "POST",
        body: JSON.stringify({
          name: formData.get("name"),
          username: formData.get("username"),
          password: formData.get("password"),
          bio: formData.get("bio"),
        }),
      });
      state.searchTerm = "";
      elements.globalSearch.value = "";
      elements.registerForm.reset();
      renderAll();
      showStatus("Account created. Welcome to Vybe.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });

  elements.logoutButton.addEventListener("click", async () => {
    try {
      await api("/api/auth/logout", {
        method: "POST",
        body: JSON.stringify({}),
      });
      state.bootstrap = await api("/api/bootstrap", {
        method: "GET",
        headers: {},
      });
      state.searchTerm = "";
      elements.globalSearch.value = "";
      state.openComments.clear();
      renderAll();
      showStatus("Logged out.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });

  elements.composerForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!state.bootstrap?.currentUser) {
      requireUserAction();
      return;
    }

    const form = new FormData();
    form.append("content", elements.composerText.value.trim());
    form.append("mood", state.selectedMood);
    form.append("audience", elements.composerAudience.value);

    const image = elements.composerImage.files?.[0];
    if (image) {
      form.append("image", image);
    }

    try {
      await api("/api/posts", {
        method: "POST",
        body: form,
      });
      await loadBootstrap();
      elements.composerForm.reset();
      elements.composerImageName.textContent = "";
      renderAll();
      showStatus("Moment posted to Vybe.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });

  elements.memoryForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const formData = new FormData(elements.memoryForm);
      await api("/api/memories", {
        method: "POST",
        body: JSON.stringify({
          type: formData.get("type"),
          title: formData.get("title"),
          badge: formData.get("badge"),
          description: formData.get("description"),
        }),
      });
      await loadBootstrap();
      elements.memoryForm.reset();
      renderAll();
      showStatus("Memory board created.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });

  elements.profileForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const payload = {
        name: elements.profileNameInput.value.trim(),
        bio: elements.profileBioInput.value.trim(),
      };

      const data = await api("/api/profile", {
        method: "POST",
        body: JSON.stringify(payload),
      });

      state.bootstrap = data.bootstrap;
      renderAll();
      showStatus("Profile updated.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });

  document.addEventListener("click", async (event) => {
    const likeButton = event.target.closest("[data-action='like']");
    if (likeButton) {
      try {
        const updated = await api(`/api/posts/${likeButton.dataset.postId}/like`, {
          method: "POST",
          body: JSON.stringify({}),
        });
        replacePost(updated);
        renderFeed();
        renderProfile();
      } catch (error) {
        showStatus(error.message, "error");
      }
      return;
    }

    const commentsButton = event.target.closest("[data-action='toggle-comments']");
    if (commentsButton) {
      const postId = commentsButton.dataset.postId;
      if (state.openComments.has(postId)) {
        state.openComments.delete(postId);
      } else {
        state.openComments.add(postId);
      }
      renderFeed();
      renderProfile();
      return;
    }

    const deleteButton = event.target.closest("[data-action='delete-post']");
    if (deleteButton) {
      const confirmed = window.confirm("Delete this post?");
      if (!confirmed) return;

      try {
        await api(`/api/posts/${deleteButton.dataset.postId}`, {
          method: "DELETE",
          body: JSON.stringify({}),
        });
        await loadBootstrap();
        renderAll();
        showStatus("Post deleted.");
      } catch (error) {
        showStatus(error.message, "error");
      }
      return;
    }

    const deleteMemoryButton = event.target.closest("[data-action='delete-memory']");
    if (deleteMemoryButton) {
      const confirmed = window.confirm("Delete this memory board?");
      if (!confirmed) return;

      try {
        await api(`/api/memories/${deleteMemoryButton.dataset.memoryId}`, {
          method: "DELETE",
          body: JSON.stringify({}),
        });
        await loadBootstrap();
        renderAll();
        showStatus("Memory board deleted.");
      } catch (error) {
        showStatus(error.message, "error");
      }
      return;
    }

    const personButton = event.target.closest("[data-action='toggle-person']");
    if (personButton) {
      const personId = personButton.dataset.personId;
      if (state.connectedPeople.has(personId)) {
        state.connectedPeople.delete(personId);
      } else {
        state.connectedPeople.add(personId);
      }
      saveUiState();
      renderPeople();
      showStatus("Your connection list was updated.");
      return;
    }

    const circleButton = event.target.closest("[data-action='toggle-circle']");
    if (circleButton) {
      const circleId = circleButton.dataset.circleId;
      if (state.joinedCircles.has(circleId)) {
        state.joinedCircles.delete(circleId);
      } else {
        state.joinedCircles.add(circleId);
      }
      saveUiState();
      renderCircles();
      showStatus("Your circles were updated.");
    }
  });

  document.addEventListener("submit", async (event) => {
    const form = event.target.closest(".comment-form");
    if (!form) return;

    event.preventDefault();
    const input = form.querySelector("input[name='comment']");
    if (!input?.value.trim()) {
      showStatus("Comment cannot be empty.", "error");
      return;
    }

    try {
      const updated = await api(`/api/posts/${form.dataset.postId}/comments`, {
        method: "POST",
        body: JSON.stringify({
          text: input.value.trim(),
        }),
      });
      replacePost(updated);
      state.openComments.add(form.dataset.postId);
      renderFeed();
      renderProfile();
      showStatus("Comment added.");
    } catch (error) {
      showStatus(error.message, "error");
    }
  });
}

function initReveal() {
  const revealItems = document.querySelectorAll(".reveal");

  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    revealItems.forEach((item) => item.classList.add("is-visible"));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.16,
      rootMargin: "0px 0px -30px 0px",
    },
  );

  revealItems.forEach((item) => observer.observe(item));
}

bindStaticEvents();
initReveal();
loadBootstrap().catch((error) => {
  showStatus(error.message, "error");
});
