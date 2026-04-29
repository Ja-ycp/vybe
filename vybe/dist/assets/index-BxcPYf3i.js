(function(){let e=document.createElement(`link`).relList;if(e&&e.supports&&e.supports(`modulepreload`))return;for(let e of document.querySelectorAll(`link[rel="modulepreload"]`))n(e);new MutationObserver(e=>{for(let t of e)if(t.type===`childList`)for(let e of t.addedNodes)e.tagName===`LINK`&&e.rel===`modulepreload`&&n(e)}).observe(document,{childList:!0,subtree:!0});function t(e){let t={};return e.integrity&&(t.integrity=e.integrity),e.referrerPolicy&&(t.referrerPolicy=e.referrerPolicy),e.crossOrigin===`use-credentials`?t.credentials=`include`:e.crossOrigin===`anonymous`?t.credentials=`omit`:t.credentials=`same-origin`,t}function n(e){if(e.ep)return;e.ep=!0;let n=t(e);fetch(e.href,n)}})();var e=`vybe-demo-posts-v1`,t=`vybe-demo-ui-v1`,n={"Campus Circle":`students`,"Creative Room":`creatives`,"Pro Network":`professionals`,"Travel Loop":`travelers`,"Family Thread":`families`},r={students:`Students`,creatives:`Creatives`,professionals:`Professionals`,travelers:`Travelers`,families:`Families`},i=[{id:`c1`,name:`Campus Pulse`,label:`Students`,members:128,description:`Thesis wins, org life, study breaks, defense-day nerves, and small campus victories.`},{id:`c2`,name:`Creative Room`,label:`Creatives`,members:84,description:`Illustration drops, photo walks, design critiques, and behind-the-scenes drafts.`},{id:`c3`,name:`Pro Network`,label:`Professionals`,members:61,description:`Career milestones, industry thoughts, internship updates, and calmer networking.`},{id:`c4`,name:`Travel Loop`,label:`Travelers`,members:42,description:`Local finds, slow itineraries, journaling, and memory-led city discovery.`}],a=[{id:`u1`,name:`Ashley Lim`,handle:`@ashlim`,role:`Illustrator`,mutual:12,summary:`Sketchbooks, café corners, and poster experiments.`},{id:`u2`,name:`Andre Cruz`,handle:`@andrecruz`,role:`Product intern`,mutual:8,summary:`Career notes and practical lessons from first-job life.`},{id:`u3`,name:`Lara Villanueva`,handle:`@laragoes`,role:`Travel diarist`,mutual:6,summary:`Quiet routes, neighborhood guides, and honest travel memories.`},{id:`u4`,name:`Dianne Reyes`,handle:`@diannehome`,role:`Family storyteller`,mutual:10,summary:`Milestones, Sunday lunches, and generational memory keeping.`},{id:`u5`,name:`Nico Flores`,handle:`@nicoframes`,role:`Student photographer`,mutual:14,summary:`Campus portraits, thesis nights, and city-light photo walks.`}],o=[{id:`m1`,type:`Student journal`,title:`Final defense week`,author:`Kathy Pepito`,description:`A collection of late-night drafts, proof-of-life snacks, and the exact moment the hard work finally clicked.`,badge:`8 moments`,tint:`rgba(194, 24, 91, 0.16)`},{id:`m2`,type:`Creative progress`,title:`Poster studies and rough cuts`,author:`Ashley Lim`,description:`Process over perfection: sketches, type trials, rejected ideas, and the pieces that taught the most.`,badge:`5 drafts`,tint:`rgba(123, 31, 162, 0.16)`},{id:`m3`,type:`Family archive`,title:`Sunday table stories`,author:`Dianne Reyes`,description:`The kind of small, warm moments that deserve to stay visible long after the meal is over.`,badge:`3 generations`,tint:`rgba(106, 145, 89, 0.14)`},{id:`m4`,type:`Travel board`,title:`Baguio in soft weather`,author:`Lara Villanueva`,description:`Foggy mornings, hidden cafés, slow walks, and the exact corners that made the city feel personal.`,badge:`6 places`,tint:`rgba(208, 154, 61, 0.16)`}],s=[{title:`Campus Pulse is growing`,body:`Students are using Vybe to post thesis wins, org updates, and finals-week survival snapshots.`},{title:`Creative Room feels active`,body:`More process posts are showing up than polished end products, which is exactly the point.`},{title:`Travel Loop is staying intentional`,body:`The focus is on local memory-making, not checklist tourism or viral destination pressure.`},{title:`Family Thread is staying warm`,body:`Milestones and ordinary days are both being shared, which makes the archive feel real.`}];function c(){let e=Date.now();return[{id:`p1`,author:`Kathy Pepito`,handle:`@kathypepito`,role:`Student founder`,group:`students`,mood:`Inspired`,audience:`Campus Circle`,content:`Turning the Vybe proposal into a real product demo today. I want a place where thesis wins, family updates, creative drafts, and travel memories can live together without feeling noisy.`,tags:[`Launch day`,`Human-first`],likes:32,liked:!1,timestamp:e-1e3*60*34,comments:[{author:`Ashley Lim`,text:`This already feels calmer than most feeds.`},{author:`Nico Flores`,text:`The rose look gives it a strong identity right away.`}]},{id:`p2`,author:`Ashley Lim`,handle:`@ashlim`,role:`Illustrator`,group:`creatives`,mood:`Curious`,audience:`Creative Room`,content:`Posting unfinished work today instead of waiting for perfect. It is nice having a space where process feels welcome.`,tags:[`Sketchbook`,`Design process`],likes:21,liked:!1,timestamp:e-1e3*60*88,comments:[{author:`Kathy Pepito`,text:`This is exactly the kind of sharing Vybe should encourage.`}]},{id:`p3`,author:`Andre Cruz`,handle:`@andrecruz`,role:`Product intern`,group:`professionals`,mood:`Celebrating`,audience:`Pro Network`,content:`Small career milestone today. I shipped my first dashboard change at work and wanted to share the win somewhere that feels human, not performative.`,tags:[`Career`,`Milestone`],likes:19,liked:!1,timestamp:e-1e3*60*60*5,comments:[{author:`Mira Santos`,text:`The honest version of career progress is always better.`}]},{id:`p4`,author:`Lara Villanueva`,handle:`@laragoes`,role:`Travel diarist`,group:`travelers`,mood:`Reflective`,audience:`Travel Loop`,content:`Found a tiny coffee shop in Baguio that felt more memorable than every popular stop on my list. Vybe would be perfect for these slower travel stories.`,tags:[`Baguio`,`Travel journal`],likes:27,liked:!1,timestamp:e-1e3*60*60*11,comments:[{author:`Kathy Pepito`,text:`This is the kind of post that makes travel feel personal again.`}]},{id:`p5`,author:`Dianne Reyes`,handle:`@diannehome`,role:`Family storyteller`,group:`families`,mood:`Celebrating`,audience:`Family Thread`,content:`Grandma remembered the old family recipe by heart today. I love that ordinary moments can matter just as much as big milestones.`,tags:[`Family`,`Memory`],likes:41,liked:!1,timestamp:e-1e3*60*60*23,comments:[{author:`Ashley Lim`,text:`This feels so warm.`}]},{id:`p6`,author:`Nico Flores`,handle:`@nicoframes`,role:`Student photographer`,group:`students`,mood:`Inspired`,audience:`Campus Circle`,content:`Shot portraits around campus after sunset and everyone looked like they were carrying a full semester in their eyes. Real moments always photograph better.`,tags:[`Photography`,`Campus life`],likes:24,liked:!1,timestamp:e-1e3*60*60*29,comments:[]}]}function l(e){try{let t=localStorage.getItem(e);return t?JSON.parse(t):null}catch{return null}}function u(e,t){try{localStorage.setItem(e,JSON.stringify(t))}catch{}}var d=l(e),f=l(t),p={activeView:`feed`,activeFilter:`all`,searchTerm:``,selectedMood:`Inspired`,posts:Array.isArray(d)?d:c(),openComments:new Set,connectedPeople:new Set(f?.connectedPeople??[]),joinedCircles:new Set(f?.joinedCircles??[])},m={composerForm:document.querySelector(`#composer-form`),composerText:document.querySelector(`#composer-text`),composerAudience:document.querySelector(`#composer-audience`),globalSearch:document.querySelector(`#global-search`),feedList:document.querySelector(`#feed-list`),circlesList:document.querySelector(`#circles-list`),peopleList:document.querySelector(`#people-list`),memoriesGrid:document.querySelector(`#memories-grid`),profilePosts:document.querySelector(`#profile-posts`),pulseList:document.querySelector(`#pulse-list`),suggestedList:document.querySelector(`#suggested-list`),heroStatPosts:document.querySelector(`#hero-stat-posts`),heroStatCircles:document.querySelector(`#hero-stat-circles`),heroStatPeople:document.querySelector(`#hero-stat-people`),viewButtons:document.querySelectorAll(`[data-view-target]`),filterButtons:document.querySelectorAll(`.filter-chip`),moodButtons:document.querySelectorAll(`.mood-chip`),appViews:document.querySelectorAll(`.app-view`),jumpButtons:document.querySelectorAll(`[data-jump='composer']`)};function h(e){return String(e).replaceAll(`&`,`&amp;`).replaceAll(`<`,`&lt;`).replaceAll(`>`,`&gt;`).replaceAll(`"`,`&quot;`).replaceAll(`'`,`&#39;`)}function g(e){return h(e).replaceAll(`
`,`<br />`)}function _(e){return e.split(` `).slice(0,2).map(e=>e[0]?.toUpperCase()??``).join(``)}function v(e){return`${e}-${Math.random().toString(36).slice(2,10)}`}function y(e){let t=Date.now()-Number(e),n=1e3*60,r=n*60,i=r*24;return t<n?`Just now`:t<r?`${Math.floor(t/n)}m ago`:t<i?`${Math.floor(t/r)}h ago`:t<i*7?`${Math.floor(t/i)}d ago`:new Date(e).toLocaleDateString(void 0,{month:`short`,day:`numeric`})}function b(){u(e,p.posts),u(t,{connectedPeople:[...p.connectedPeople],joinedCircles:[...p.joinedCircles]})}function x(e){p.activeView=e,m.appViews.forEach(t=>{let n=t.dataset.view===e;t.hidden=!n,t.classList.toggle(`is-active`,n)}),m.viewButtons.forEach(t=>{t.classList.toggle(`is-active`,t.dataset.viewTarget===e)})}function S(){m.heroStatPosts.textContent=String(p.posts.length),m.heroStatCircles.textContent=String(i.length),m.heroStatPeople.textContent=String(a.length)}function C(e,t){return t?[e.author,e.role,e.handle,e.content,e.mood,e.audience,...e.tags??[]].join(` `).toLowerCase().includes(t):!0}function w(e,t){return t?`${e.name} ${e.label} ${e.description}`.toLowerCase().includes(t):!0}function T(e,t){return t?`${e.name} ${e.handle} ${e.role} ${e.summary}`.toLowerCase().includes(t):!0}function E(e,t){return t?`${e.type} ${e.title} ${e.author} ${e.description}`.toLowerCase().includes(t):!0}function D(e,t){return`
    <article class="panel empty-state">
      <strong>${h(e)}</strong>
      <p class="muted">${h(t)}</p>
    </article>
  `}function O(e){let t=p.openComments.has(e.id);return`
    <article class="panel feed-card" data-group="${h(e.group)}">
      <div class="feed-head">
        <div class="feed-meta">
          <div class="avatar">${h(_(e.author))}</div>
          <div>
            <strong>${h(e.author)}</strong>
            <div class="comment-meta">
              ${h(e.role)} · <span class="handle">${h(e.handle)}</span>
            </div>
          </div>
        </div>

        <div>
          <div class="meta-pill">${h(r[e.group]??e.group)}</div>
          <div class="post-time">${h(y(e.timestamp))}</div>
        </div>
      </div>

      <p class="feed-content">${g(e.content)}</p>

      <div class="tag-row">
        ${(e.tags??[]).map(e=>`<span class="tag">${h(e)}</span>`).join(``)}
        <span class="tag">${h(e.mood)}</span>
        <span class="tag">${h(e.audience)}</span>
      </div>

      <div class="card-actions">
        <button
          class="action-button ${e.liked?`is-active`:``}"
          data-action="like"
          data-post-id="${h(e.id)}"
          type="button"
        >
          Like ${h(String(e.likes))}
        </button>
        <button
          class="action-button ${t?`is-active`:``}"
          data-action="toggle-comments"
          data-post-id="${h(e.id)}"
          type="button"
        >
          Comments ${h(String(e.comments.length))}
        </button>
        <span class="comment-count">${h(e.audience)} · ${h(e.mood)}</span>
      </div>

      ${t?`
            <div class="comments-wrap">
              ${e.comments.length?e.comments.map(e=>`
                          <article class="comment-item">
                            <strong>${h(e.author)}</strong>
                            <div class="comment-body">${h(e.text)}</div>
                          </article>
                        `).join(``):`<p class="muted">No comments yet. Start the conversation.</p>`}

              <form class="comment-form" data-post-id="${h(e.id)}">
                <input
                  type="text"
                  name="comment"
                  placeholder="Write a comment"
                  maxlength="180"
                  autocomplete="off"
                />
                <button class="button-primary" type="submit">Reply</button>
              </form>
            </div>
          `:``}
    </article>
  `}function k(){let e=p.posts.filter(e=>(p.activeFilter===`all`||e.group===p.activeFilter)&&C(e,p.searchTerm)).sort((e,t)=>Number(t.timestamp)-Number(e.timestamp));m.feedList.innerHTML=e.length?e.map(O).join(``):D(`No moments match this view.`,`Try another filter or search for a different person, circle, or mood.`)}function A(){let e=i.filter(e=>w(e,p.searchTerm));m.circlesList.innerHTML=e.length?e.map(e=>{let t=p.joinedCircles.has(e.id);return`
            <article class="circle-card">
              <div class="circle-row">
                <div>
                  <span>${h(e.label)}</span>
                  <h4>${h(e.name)}</h4>
                </div>
                <button
                  class="ghost-button ${t?`is-active`:``}"
                  data-action="toggle-circle"
                  data-circle-id="${h(e.id)}"
                  type="button"
                >
                  ${t?`Joined`:`Join`}
                </button>
              </div>
              <p class="circle-text">${h(e.description)}</p>
              <div class="tag-row">
                <span class="tag">${h(String(e.members))} members</span>
              </div>
            </article>
          `}).join(``):D(`No circles found.`,`Try a broader search term.`)}function j(){let e=a.filter(e=>T(e,p.searchTerm));m.peopleList.innerHTML=e.length?e.map(e=>{let t=p.connectedPeople.has(e.id);return`
            <article class="person-card">
              <div class="person-row">
                <div class="user-chip">
                  <div class="avatar">${h(_(e.name))}</div>
                  <div>
                    <h4>${h(e.name)}</h4>
                    <div class="comment-meta">${h(e.handle)} · ${h(e.role)}</div>
                  </div>
                </div>
                <button
                  class="ghost-button ${t?`is-active`:``}"
                  data-action="toggle-person"
                  data-person-id="${h(e.id)}"
                  type="button"
                >
                  ${t?`Connected`:`Connect`}
                </button>
              </div>
              <p class="person-text">${h(e.summary)}</p>
              <div class="tag-row">
                <span class="tag">${h(String(e.mutual))} mutuals</span>
              </div>
            </article>
          `}).join(``):D(`No people found.`,`Try searching by role, name, or handle.`)}function M(){let e=o.filter(e=>E(e,p.searchTerm));m.memoriesGrid.innerHTML=e.length?e.map(e=>`
            <article class="memory-card" style="--memory-tint:${h(e.tint)}">
              <div class="memory-top">
                <span class="memory-type">${h(e.type)}</span>
                <div class="memory-badge">${h(e.badge)}</div>
              </div>
              <h4>${h(e.title)}</h4>
              <p>${h(e.description)}</p>
              <div class="memory-footer">
                <span class="comment-meta">by ${h(e.author)}</span>
                <span class="tag">Genuine memory</span>
              </div>
            </article>
          `).join(``):D(`No memory boards found.`,`Try searching for a title, author, or type.`)}function N(){let e=p.posts.filter(e=>e.author===`Kathy Pepito`&&C(e,p.searchTerm)).sort((e,t)=>Number(t.timestamp)-Number(e.timestamp));m.profilePosts.innerHTML=e.length?e.map(O).join(``):D(`No profile posts match your search.`,`Try clearing the search box to see Kathy's full activity.`)}function P(){m.pulseList.innerHTML=s.map(e=>`
        <article class="pulse-item">
          <strong>${h(e.title)}</strong>
          <p>${h(e.body)}</p>
        </article>
      `).join(``)}function F(){m.suggestedList.innerHTML=a.slice(0,3).map(e=>`
        <article class="suggested-item">
          <strong>${h(e.name)}</strong>
          <p>${h(e.role)} · ${h(String(e.mutual))} mutuals</p>
        </article>
      `).join(``)}function I(){S(),k(),A(),j(),M(),N(),P(),F()}function L(e){let t=m.composerAudience.value,i=n[t]??`students`;p.posts.unshift({id:v(`post`),author:`Kathy Pepito`,handle:`@kathypepito`,role:`Student founder`,group:i,mood:p.selectedMood,audience:t,content:e,tags:[`New post`,r[i]],likes:0,liked:!1,timestamp:Date.now(),comments:[]}),b(),I()}function R(e){let t=p.posts.find(t=>t.id===e);t&&(t.liked=!t.liked,t.likes+=t.liked?1:-1,b(),I())}function z(e){p.openComments.has(e)?p.openComments.delete(e):p.openComments.add(e),I()}function B(e,t){let n=t.trim();if(!n)return;let r=p.posts.find(t=>t.id===e);r&&(r.comments.push({author:`Kathy Pepito`,text:n}),p.openComments.add(e),b(),I())}function V(e){p.connectedPeople.has(e)?p.connectedPeople.delete(e):p.connectedPeople.add(e),b(),j()}function H(e){p.joinedCircles.has(e)?p.joinedCircles.delete(e):p.joinedCircles.add(e),b(),A()}function U(){m.viewButtons.forEach(e=>{e.addEventListener(`click`,()=>{x(e.dataset.viewTarget),window.scrollTo({top:0,behavior:`smooth`})})}),m.filterButtons.forEach(e=>{e.addEventListener(`click`,()=>{p.activeFilter=e.dataset.filter,m.filterButtons.forEach(t=>t.classList.toggle(`is-active`,t===e)),k()})}),m.moodButtons.forEach(e=>{e.addEventListener(`click`,()=>{p.selectedMood=e.dataset.mood,m.moodButtons.forEach(t=>t.classList.toggle(`is-selected`,t===e))})}),m.globalSearch?.addEventListener(`input`,e=>{p.searchTerm=e.target.value.trim().toLowerCase(),I()}),m.composerForm?.addEventListener(`submit`,e=>{e.preventDefault();let t=m.composerText.value.trim();if(!t){m.composerText.focus();return}L(t),m.composerText.value=``,x(`feed`),m.composerText.focus()}),m.jumpButtons.forEach(e=>{e.addEventListener(`click`,()=>{x(`feed`),document.querySelector(`#composer`)?.scrollIntoView({behavior:`smooth`,block:`start`}),m.composerText?.focus()})}),document.addEventListener(`click`,e=>{let t=e.target.closest(`[data-action='like']`);if(t){R(t.dataset.postId);return}let n=e.target.closest(`[data-action='toggle-comments']`);if(n){z(n.dataset.postId);return}let r=e.target.closest(`[data-action='toggle-person']`);if(r){V(r.dataset.personId);return}let i=e.target.closest(`[data-action='toggle-circle']`);i&&H(i.dataset.circleId)}),document.addEventListener(`submit`,e=>{let t=e.target.closest(`.comment-form`);if(!t)return;e.preventDefault();let n=t.querySelector(`input[name='comment']`);n&&B(t.dataset.postId,n.value)})}function W(){let e=document.querySelectorAll(`.reveal`);if(window.matchMedia(`(prefers-reduced-motion: reduce)`).matches){e.forEach(e=>e.classList.add(`is-visible`));return}let t=new IntersectionObserver(e=>{e.forEach(e=>{e.isIntersecting&&(e.target.classList.add(`is-visible`),t.unobserve(e.target))})},{threshold:.16,rootMargin:`0px 0px -30px 0px`});e.forEach(e=>t.observe(e))}x(`feed`),I(),U(),W();