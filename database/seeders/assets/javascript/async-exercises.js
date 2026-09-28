/**
 * Asynchronous JavaScript — Exercises
 * Course: Complete JavaScript: From Fundamentals to Modern JS
 *
 * Run with Node.js 18+ (it has fetch built in):  node async-exercises.js
 * The public API used here is https://jsonplaceholder.typicode.com (free, no key).
 */

const API = "https://jsonplaceholder.typicode.com";

// 1. Predict the output order BEFORE running, then uncomment and check.
//    Write your prediction here: ...
// console.log("A");
// setTimeout(() => console.log("B"), 0);
// Promise.resolve().then(() => console.log("C"));
// console.log("D");

// 2. Return a promise that resolves after `ms` milliseconds.
function wait(ms) {
  // TODO
}

// 3. Fetch user #id and return ONLY their name. Throw an Error if the response is not ok.
async function getUserName(id) {
  // TODO
}

// 4. Fetch users 1, 2 and 3 IN PARALLEL and return an array of their names.
async function getManyNames(ids) {
  // TODO (hint: Promise.all)
}

// 5. Fetch all posts of user #id and return how many there are.
//    Endpoint: `${API}/posts?userId=${id}`
async function countPosts(id) {
  // TODO
}

// 6. Wrap any promise with a timeout: reject with Error("Timeout") if it takes longer than `ms`.
function withTimeout(promise, ms) {
  // TODO (hint: Promise.race)
}

// 7. Retry an async function up to `times` times before giving up.
async function retry(fn, times) {
  // TODO
}

// ---------------------------------------------------------------------------
// Checks
// ---------------------------------------------------------------------------
(async () => {
  const log = (label, ok) => console.log(`${ok ? "PASS" : "FAIL"}  ${label}`);
  try {
    const start = Date.now();
    await wait(300);
    log("wait(300)", Date.now() - start >= 290);

    log("getUserName(1)", (await getUserName(1)) === "Leanne Graham");
    const names = await getManyNames([1, 2, 3]);
    log("getManyNames", JSON.stringify(names) === JSON.stringify(["Leanne Graham", "Ervin Howell", "Clementine Bauch"]));
    log("countPosts(1)", (await countPosts(1)) === 10);

    const slow = new Promise((resolve) => setTimeout(() => resolve("done"), 1000));
    await withTimeout(slow, 100).then(
      () => log("withTimeout", false),
      (err) => log("withTimeout", err.message === "Timeout"),
    );

    let attempts = 0;
    const flaky = async () => { attempts++; if (attempts < 3) throw new Error("fail"); return "ok"; };
    log("retry", (await retry(flaky, 5)) === "ok" && attempts === 3);
  } catch (err) {
    console.log("FAIL  unexpected error:", err.message);
  }
})();

/* ---------------------------------------------------------------------------
   SOLUTIONS
   ---------------------------------------------------------------------------
// 1. Output: A, D, C, B  (sync first, then microtasks, then tasks)
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
async function getUserName(id) {
  const res = await fetch(`${API}/users/${id}`);
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  const user = await res.json();
  return user.name;
}
const getManyNames = (ids) => Promise.all(ids.map(getUserName));
async function countPosts(id) {
  const res = await fetch(`${API}/posts?userId=${id}`);
  const posts = await res.json();
  return posts.length;
}
function withTimeout(promise, ms) {
  const timeout = new Promise((_, reject) => setTimeout(() => reject(new Error("Timeout")), ms));
  return Promise.race([promise, timeout]);
}
async function retry(fn, times) {
  let lastError;
  for (let i = 0; i < times; i++) {
    try { return await fn(); } catch (err) { lastError = err; }
  }
  throw lastError;
}
*/
