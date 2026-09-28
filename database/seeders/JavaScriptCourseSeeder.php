<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\CourseModule;
use App\Models\CourseSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds a complete, active "JavaScript" course (modules, sections and materials).
 *
 * If a course titled "JavaScript" already exists it is reused (keeping its thumbnail)
 * and its modules are replaced, so the seeder can be run multiple times safely:
 *   php artisan db:seed --class=JavaScriptCourseSeeder
 */
class JavaScriptCourseSeeder extends Seeder
{
    private const ASSET_DIR = __DIR__ . '/assets/javascript';
    private const STORAGE_DIR = 'course-materials/javascript';

    public function run(): void
    {
        $this->publishAssets();

        DB::transaction(function () {
            $course = Course::where('title', 'JavaScript')->first()
                ?? new Course(['title' => 'JavaScript', 'created_by' => 1]);

            $authorId = $course->created_by ?? 1;

            $course->fill([
                'description' => $this->courseDescription(),
                'status' => 1,
                'nr_of_files' => 0,
                'duration' => 0,
                'updated_by' => $authorId,
            ])->save();

            $course->modules()->delete();

            $courseFiles = 0;
            $courseDuration = 0;

            foreach ($this->modules() as $moduleData) {
                $module = CourseModule::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'],
                ]);

                $moduleFiles = 0;
                $moduleDuration = 0;

                foreach ($moduleData['sections'] as $sectionData) {
                    $sectionFiles = collect($sectionData['materials'])
                        ->whereIn('type', ['image', 'file'])
                        ->count();
                    $sectionDuration = $sectionData['minutes'] * 60;

                    $section = CourseSection::create([
                        'module_id' => $module->id,
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'],
                        'nr_of_files' => $sectionFiles,
                        'duration' => $sectionDuration,
                    ]);

                    foreach ($sectionData['materials'] as $index => $material) {
                        CourseMaterial::create([
                            'course_section_id' => $section->id,
                            'title' => $material['title'],
                            'type' => $material['type'],
                            'content' => $material['content'],
                            'material_url' => $material['material_url'],
                            'sort_order' => $index + 1,
                            'created_by' => $authorId,
                        ]);
                    }

                    $moduleFiles += $sectionFiles;
                    $moduleDuration += $sectionDuration;
                }

                $module->update(['nr_of_files' => $moduleFiles, 'duration' => $moduleDuration]);

                $courseFiles += $moduleFiles;
                $courseDuration += $moduleDuration;
            }

            $course->update(['nr_of_files' => $courseFiles, 'duration' => $courseDuration]);
        });
    }

    /**
     * Copy diagrams and downloadable files into the public storage disk.
     */
    private function publishAssets(): void
    {
        foreach (glob(self::ASSET_DIR . '/*') as $path) {
            Storage::disk('public')->put(self::STORAGE_DIR . '/' . basename($path), file_get_contents($path));
        }
    }

    private function text(string $title, string $content): array
    {
        return ['title' => $title, 'type' => 'text', 'content' => trim($content), 'material_url' => null];
    }

    private function image(string $title, string $file, string $caption): array
    {
        return ['title' => $title, 'type' => 'image', 'content' => $caption, 'material_url' => self::STORAGE_DIR . '/' . $file];
    }

    private function file(string $title, string $file, string $description): array
    {
        return ['title' => $title, 'type' => 'file', 'content' => $description, 'material_url' => self::STORAGE_DIR . '/' . $file];
    }

    private function courseDescription(): string
    {
        return 'JavaScript is the programming language of the web and one of the most in-demand skills in software development. '
            . 'This course takes you from your very first line of code to writing modern, professional JavaScript. '
            . 'You will learn the core language (variables, data types, operators, control flow and functions), master arrays and objects, '
            . 'understand scope, closures and the "this" keyword, and bring web pages to life by manipulating the DOM and handling events. '
            . 'Next you will learn asynchronous programming with callbacks, Promises and async/await, and fetch real data from APIs. '
            . 'Finally you will write clean, maintainable code with ES modules, classes, error handling and debugging techniques. '
            . 'Every module includes explanations with code examples, visual diagrams, downloadable exercises with solutions and a cheat sheet, '
            . 'and the course ends with a real project: a Weather Dashboard built with vanilla JavaScript. '
            . 'No prior programming experience is required — only basic knowledge of HTML and CSS.';
    }

    private function modules(): array
    {
        return [
            // -----------------------------------------------------------------
            // MODULE 1
            // -----------------------------------------------------------------
            [
                'title' => 'Module 1: Getting Started with JavaScript',
                'description' => 'Understand what JavaScript is, where it runs, and set up a professional development environment. By the end of this module you will write and run your first JavaScript program both in the browser and in Node.js.',
                'sections' => [
                    [
                        'title' => 'What is JavaScript?',
                        'description' => 'A short history of the language, what it is used for today and how it relates to HTML, CSS and ECMAScript.',
                        'minutes' => 15,
                        'materials' => [
                            $this->text('JavaScript in a Nutshell', <<<'TXT'
JavaScript (JS) is a high-level, dynamically typed, interpreted (just-in-time compiled) programming language. It was created by Brendan Eich at Netscape in 1995 in just 10 days, and today it runs on almost every device with a web browser.

The three layers of the web:
• HTML — the structure (headings, paragraphs, buttons)
• CSS — the presentation (colors, layout, fonts)
• JavaScript — the behavior (reacting to clicks, loading data, updating the page)

Where JavaScript runs today:
• Browsers — Chrome (V8 engine), Firefox (SpiderMonkey), Safari (JavaScriptCore)
• Servers — Node.js, Deno, Bun
• Mobile apps — React Native, Ionic
• Desktop apps — Electron (VS Code, Slack, Discord are built with it)
• Even databases, IoT devices and serverless functions

JavaScript vs ECMAScript:
ECMAScript is the official specification of the language, maintained by the TC39 committee. JavaScript is the implementation of that specification. Since 2015 (ES6 / ES2015) a new version is released every year — ES2016, ES2017 … ES2024. When people say "modern JavaScript" they usually mean ES6 and later: let/const, arrow functions, classes, modules, Promises, async/await and more.

JavaScript is NOT Java. The name was a marketing decision in 1995; the two languages are completely different.
TXT),
                            $this->text('Key Characteristics of the Language', <<<'TXT'
1. Dynamically typed — variables do not have a fixed type:
let value = 42;
value = "now I am a string";   // allowed

2. Multi-paradigm — you can write procedural, object-oriented and functional code.

3. First-class functions — functions are values: they can be stored in variables, passed as arguments and returned from other functions.

4. Single-threaded with an event loop — JavaScript executes one thing at a time, but handles slow operations (network, timers) asynchronously so the page never freezes. You will learn this in Module 6.

5. Prototype-based — objects inherit directly from other objects. The "class" keyword (Module 7) is a friendlier syntax on top of prototypes.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Setting Up Your Development Environment',
                        'description' => 'Install the tools professional JavaScript developers use every day and learn to use the browser DevTools console.',
                        'minutes' => 20,
                        'materials' => [
                            $this->text('Tools You Need', <<<'TXT'
1. A modern browser — Google Chrome or Firefox.
   Open the Developer Tools with F12 (or Ctrl + Shift + I / Cmd + Option + I on Mac) and select the "Console" tab. You can type JavaScript there and press Enter to run it immediately.

2. Visual Studio Code — free code editor: https://code.visualstudio.com
   Recommended extensions:
   • Live Server — reloads the page automatically when you save
   • Prettier — formats your code consistently
   • ESLint — finds bugs and bad patterns while you type

3. Node.js (LTS version) — runs JavaScript outside the browser: https://nodejs.org
   After installing, verify in a terminal:
   node --version
   npm --version

   Run a file with:
   node app.js

4. (Optional) Git — to version your code and publish projects on GitHub.
TXT),
                            $this->text('Your First Commands in the Console', <<<'TXT'
Open the browser console and try these one by one:

console.log("Hello, JavaScript!");
2 + 3 * 4
"Java" + "Script"
typeof 42
Math.random()
new Date().getFullYear()
alert("This is a popup");

Useful console methods:
console.log()   — general output
console.warn()  — yellow warning
console.error() — red error
console.table() — shows arrays/objects as a table
console.clear() — clears the console

Tip: press the Up arrow key to repeat the previous command.
TXT),
                            $this->file('JavaScript Cheat Sheet', 'js-cheatsheet.html', 'A one-page quick reference for the whole course: variables, types, strings, loops, functions, array methods, objects, DOM, events, Promises, classes and modules. Open it in your browser and print it (Ctrl + P) or save it as PDF.'),
                        ],
                    ],
                    [
                        'title' => 'Your First JavaScript Program',
                        'description' => 'Learn the three ways to add JavaScript to a web page, write your first script and follow basic syntax rules.',
                        'minutes' => 20,
                        'materials' => [
                            $this->text('Adding JavaScript to a Web Page', <<<'TXT'
1. Inline (avoid — mixes behavior with HTML):
<button onclick="alert('Hi!')">Click</button>

2. Internal <script> tag:
<script>
  console.log("Hello from the page");
</script>

3. External file (recommended):
<script src="app.js" defer></script>

Why "defer"? The browser reads HTML from top to bottom. Without defer, a script in the <head> runs before the page elements exist, so document.querySelector() returns null. With defer, the script downloads in parallel and runs after the HTML is fully parsed. Alternative: place the <script> tag right before </body>.

For ES modules use:
<script type="module" src="main.js"></script>   (modules are deferred automatically)
TXT),
                            $this->text('Hands-on: Hello World Page', <<<'TXT'
Create a folder "hello-js" with two files.

index.html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Hello JS</title>
  <script src="app.js" defer></script>
</head>
<body>
  <h1 id="title">Hello!</h1>
  <button id="btn">Say hi</button>
</body>
</html>

app.js
const title = document.getElementById("title");
const button = document.getElementById("btn");

button.addEventListener("click", () => {
  const name = prompt("What is your name?");
  title.textContent = `Hello, ${name}!`;
});

Open index.html with Live Server, click the button and watch the heading change. Congratulations — you just wrote an interactive web page!
TXT),
                            $this->text('Syntax Basics and Comments', <<<'TXT'
• Statements usually end with a semicolon ; (JavaScript can insert them automatically, but writing them avoids surprises).
• JavaScript is case-sensitive: myName and myname are different variables.
• Code blocks are wrapped in curly braces { }.
• Whitespace and indentation are ignored by the engine but essential for humans.

Comments:
// single-line comment

/*
   multi-line
   comment
*/

/**
 * JSDoc comment — documents a function.
 * @param {number} a
 * @returns {number}
 */

Naming conventions:
• camelCase for variables and functions: userName, getTotal()
• PascalCase for classes: ShoppingCart
• UPPER_SNAKE_CASE for constants that never change: MAX_USERS
TXT),
                        ],
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // MODULE 2
            // -----------------------------------------------------------------
            [
                'title' => 'Module 2: Language Fundamentals',
                'description' => 'The building blocks of every program: variables, data types, operators, type conversion, conditions and loops.',
                'sections' => [
                    [
                        'title' => 'Variables and Data Types',
                        'description' => 'Declare variables with let and const, and learn the eight data types of JavaScript.',
                        'minutes' => 30,
                        'materials' => [
                            $this->text('let, const and var', <<<'TXT'
A variable is a named container for a value.

const — the binding cannot be reassigned. Use it by default.
const country = "Kosovo";
country = "Albania";   // TypeError: Assignment to constant variable

let — can be reassigned. Use it when the value must change.
let score = 0;
score = score + 10;

var — the old way (before 2015). It is function-scoped and hoisted, which causes bugs. Avoid it in new code.

Important: const does NOT make objects immutable, it only prevents reassigning the variable:
const user = { name: "Ana" };
user.name = "Arta";    // allowed
user = {};             // TypeError

Rule of thumb: always start with const, switch to let only when you need to reassign.
TXT),
                            $this->image('Diagram: Primitive vs Reference Types', 'js-data-types.svg', 'The seven primitive types and the object type.'),
                            $this->text('The 8 Data Types', <<<'TXT'
Primitives (immutable, copied by value):
1. string    — "text", 'text', `template ${expression}`
2. number    — 42, -7, 3.14, NaN, Infinity (integers and decimals are the same type)
3. bigint    — 123n, for integers larger than 2^53 - 1
4. boolean   — true / false
5. undefined — a declared variable that has no value yet
6. null      — intentionally "empty"
7. symbol    — unique identifier, Symbol("id")

Reference type:
8. object    — {}, [], functions, dates, maps, sets…

Checking a type:
typeof "hi"       // "string"
typeof 10         // "number"
typeof true       // "boolean"
typeof undefined  // "undefined"
typeof null       // "object"  ← famous bug, kept for compatibility
typeof []         // "object"  → use Array.isArray([])
typeof function(){} // "function"

Value vs reference:
let a = 5;  let b = a;  b = 10;           // a is still 5
const x = { n: 1 };  const y = x;  y.n = 2;  // x.n is now 2 — same object!
TXT),
                            $this->text('Working with Strings and Numbers', <<<'TXT'
Template literals (backticks) allow expressions and multiple lines:
const name = "Ana";
const age = 25;
console.log(`${name} is ${age} years old. Next year: ${age + 1}`);

Common string methods (strings are immutable — methods return NEW strings):
"JavaScript".length              // 10
"JavaScript".toUpperCase()       // "JAVASCRIPT"
"JavaScript".includes("Script")  // true
"JavaScript".slice(0, 4)         // "Java"
"  hi  ".trim()                  // "hi"
"a,b,c".split(",")               // ["a", "b", "c"]
"ha".repeat(3)                   // "hahaha"

Numbers:
0.1 + 0.2              // 0.30000000000000004 (floating point!)
(0.1 + 0.2).toFixed(2) // "0.30"
Math.round(4.6)        // 5
Math.floor(4.6)        // 4
Math.max(3, 9, 1)      // 9
Math.floor(Math.random() * 10) + 1   // random integer 1–10
Number("42")           // 42
parseInt("42px")       // 42
Number("abc")          // NaN
TXT),
                        ],
                    ],
                    [
                        'title' => 'Operators and Type Coercion',
                        'description' => 'Arithmetic, comparison and logical operators, plus the rules JavaScript uses to convert between types.',
                        'minutes' => 25,
                        'materials' => [
                            $this->text('Operators', <<<'TXT'
Arithmetic: +  -  *  /  %  (remainder)  ** (power)
10 % 3   // 1
2 ** 10  // 1024

Assignment: =  +=  -=  *=  /=  ++  --
let n = 5;  n += 2;  // 7
n++;                 // 8

Comparison: >  <  >=  <=  ===  !==  ==  !=

Logical:
&&  AND — true if both are true
||  OR  — true if at least one is true
!   NOT — inverts a boolean

Modern operators:
??   nullish coalescing — uses the right side only if the left is null or undefined
const port = config.port ?? 3000;

?.   optional chaining — stops safely if something is null/undefined
const city = user?.address?.city;   // undefined instead of an error

Ternary (short if/else):
const status = age >= 18 ? "adult" : "minor";
TXT),
                            $this->text('Truthy, Falsy and == vs ===', <<<'TXT'
In a boolean context (if, &&, ||, !) every value is converted to true or false.

There are only 8 FALSY values:
false, 0, -0, 0n, "" (empty string), null, undefined, NaN

EVERYTHING else is truthy, including: "0", "false", [], {}, and functions.

== (loose equality) converts types before comparing — confusing:
0 == ""          // true
"1" == 1         // true
null == undefined // true
[] == false      // true

=== (strict equality) compares value AND type — predictable:
0 === ""   // false
"1" === 1  // false

RULE: always use === and !==.

Implicit coercion with + :
"5" + 2   // "52"  (string concatenation wins)
"5" - 2   // 3     (- converts to number)
"5" * "2" // 10

Be explicit instead: Number("5") + 2 // 7

Short-circuit tricks:
const name = input || "Guest";      // default for any falsy value
isLoggedIn && showDashboard();      // run only if true
TXT),
                        ],
                    ],
                    [
                        'title' => 'Control Flow: Conditions and Loops',
                        'description' => 'Make decisions with if/else and switch, and repeat work with for, while, for...of and for...in.',
                        'minutes' => 35,
                        'materials' => [
                            $this->text('Conditional Statements', <<<'TXT'
if / else if / else:
const hour = new Date().getHours();

if (hour < 12) {
  console.log("Good morning");
} else if (hour < 18) {
  console.log("Good afternoon");
} else {
  console.log("Good evening");
}

switch — compares one value against many cases with ===:
switch (day) {
  case "Saturday":
  case "Sunday":
    console.log("Weekend!");
    break;
  case "Friday":
    console.log("Almost weekend");
    break;
  default:
    console.log("Workday");
}
Do not forget break — otherwise execution "falls through" into the next case.

Guard clauses make code flatter and easier to read:
function withdraw(balance, amount) {
  if (amount <= 0) return "Invalid amount";
  if (amount > balance) return "Insufficient funds";
  return balance - amount;
}
TXT),
                            $this->text('Loops', <<<'TXT'
Classic for — when you need an index:
for (let i = 1; i <= 5; i++) {
  console.log(`Step ${i}`);
}

while — when you don't know how many iterations:
let dice = 0;
while (dice !== 6) {
  dice = Math.floor(Math.random() * 6) + 1;
  console.log(dice);
}

do...while — runs at least once.

for...of — iterate the VALUES of arrays, strings, maps, sets:
for (const fruit of ["apple", "pear", "plum"]) {
  console.log(fruit);
}

for...in — iterate the KEYS of an object:
const user = { name: "Ana", age: 25 };
for (const key in user) {
  console.log(key, user[key]);
}

break exits the loop, continue skips to the next iteration:
for (let i = 1; i <= 10; i++) {
  if (i % 2 === 0) continue;  // skip even numbers
  if (i > 7) break;           // stop after 7
  console.log(i);             // 1 3 5 7
}

Warning: a loop whose condition never becomes false is an infinite loop and freezes the tab.
TXT),
                            $this->file('Exercises: Fundamentals (with solutions)', 'fundamentals-exercises.js', '7 exercises on types, operators, conditions and loops with automatic PASS/FAIL checks. Run it with: node fundamentals-exercises.js. Solutions are at the bottom of the file — try on your own first!'),
                        ],
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // MODULE 3
            // -----------------------------------------------------------------
            [
                'title' => 'Module 3: Functions, Scope and Closures',
                'description' => 'Functions are the heart of JavaScript. Learn every way to create them, how scope and hoisting work, what closures are and how the "this" keyword is determined.',
                'sections' => [
                    [
                        'title' => 'Declaring and Calling Functions',
                        'description' => 'Function declarations, expressions and arrow functions; parameters, default values, rest parameters and return values.',
                        'minutes' => 30,
                        'materials' => [
                            $this->text('Three Ways to Create a Function', <<<'TXT'
1. Function declaration — hoisted, can be called before it is defined:
function add(a, b) {
  return a + b;
}

2. Function expression — stored in a variable:
const multiply = function (a, b) {
  return a * b;
};

3. Arrow function (ES6) — shorter syntax:
const square = (x) => x * x;          // implicit return
const greet = () => console.log("Hi");
const makeUser = (name) => ({ name });  // return an object: wrap in ( )
const sum = (a, b) => {
  const result = a + b;
  return result;                        // with { } you need return
};

Arrow functions do NOT have their own "this" or "arguments" — great for callbacks, not for object methods.
TXT),
                            $this->text('Parameters and Return Values', <<<'TXT'
Default parameters:
function greet(name = "Guest") {
  return `Hello, ${name}`;
}
greet();        // "Hello, Guest"

Rest parameters — collect any number of arguments into an array:
function average(...numbers) {
  const total = numbers.reduce((sum, n) => sum + n, 0);
  return total / numbers.length;
}
average(4, 8, 6);   // 6

Destructuring parameters — great for "options" objects:
function createUser({ name, role = "student", active = true }) {
  return { name, role, active };
}
createUser({ name: "Arta" });

Return:
• A function without return (or with an empty return) returns undefined.
• return immediately stops the function.

Functions as values (higher-order functions):
function repeat(times, action) {
  for (let i = 0; i < times; i++) action(i);
}
repeat(3, (i) => console.log(`Run #${i}`));

A good function does ONE thing, has a descriptive verb name (calculateTotal, isValidEmail) and avoids changing variables outside of itself.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Scope, Hoisting and Closures',
                        'description' => 'Where variables live, why some are available before declaration, and how functions remember their environment.',
                        'minutes' => 35,
                        'materials' => [
                            $this->text('Scope and Hoisting', <<<'TXT'
Scope decides where a variable is visible.

• Global scope — declared outside any function/block; visible everywhere.
• Function scope — declared inside a function; visible only inside it.
• Block scope — let and const declared inside { } are visible only in that block.

if (true) {
  let a = 1;
  var b = 2;
}
console.log(b);  // 2   (var ignores block scope!)
console.log(a);  // ReferenceError

Hoisting — declarations are processed before code runs:
• Function declarations are fully hoisted (callable before definition).
• var is hoisted and initialized with undefined.
• let / const are hoisted but NOT initialized — accessing them before the declaration throws a ReferenceError. That period is called the Temporal Dead Zone (TDZ).

console.log(x);  // undefined
var x = 5;

console.log(y);  // ReferenceError
let y = 5;
TXT),
                            $this->image('Diagram: The Scope Chain', 'js-scope-chain.svg', 'Variable lookup always goes from the inner scope outward to the global scope.'),
                            $this->text('Closures', <<<'TXT'
A closure is a function that remembers the variables of the scope where it was created, even after that outer function has finished.

function makeCounter() {
  let count = 0;             // private variable
  return function () {
    count++;
    return count;
  };
}

const counterA = makeCounter();
counterA();   // 1
counterA();   // 2

const counterB = makeCounter();
counterB();   // 1  — each call creates a new, independent "count"

Real-world uses:
1. Data privacy — "count" cannot be changed from outside.
2. Function factories:
   const multiplier = (factor) => (n) => n * factor;
   const double = multiplier(2);
   double(21);   // 42
3. Event handlers and callbacks that need to remember values.

Classic interview question:
for (var i = 0; i < 3; i++) setTimeout(() => console.log(i), 100);   // 3 3 3
for (let i = 0; i < 3; i++) setTimeout(() => console.log(i), 100);   // 0 1 2
With let, every iteration gets its own binding, so each closure remembers a different i.
TXT),
                        ],
                    ],
                    [
                        'title' => 'The "this" Keyword',
                        'description' => 'Understand how the value of "this" is decided at call time, and control it with call, apply and bind.',
                        'minutes' => 25,
                        'materials' => [
                            $this->text('How "this" Is Determined', <<<'TXT'
"this" is decided by HOW a function is called, not where it is written.

1. Method call — this is the object before the dot:
const user = {
  name: "Ana",
  greet() { return `Hi, I am ${this.name}`; },
};
user.greet();   // "Hi, I am Ana"

2. Plain function call — this is undefined in strict mode (and in modules/classes), or the global object in sloppy mode:
const greet = user.greet;
greet();        // TypeError / "Hi, I am undefined" — the method lost its object!

3. Constructor call with new — this is the newly created object.

4. Arrow functions have NO own this — they use this from the surrounding scope:
const timer = {
  seconds: 0,
  start() {
    setInterval(() => {
      this.seconds++;           // works: "this" is timer
    }, 1000);
  },
};

5. Event listeners with a regular function — this is the element that received the event.
TXT),
                            $this->text('call, apply and bind', <<<'TXT'
You can set "this" explicitly:

function introduce(greeting, punctuation) {
  return `${greeting}, I am ${this.name}${punctuation}`;
}
const person = { name: "Driton" };

introduce.call(person, "Hello", "!");     // arguments one by one
introduce.apply(person, ["Hello", "!"]);  // arguments as an array

const bound = introduce.bind(person);    // returns a NEW function with "this" fixed
bound("Hey", ".");                         // "Hey, I am Driton."

Typical use of bind — passing a method as a callback:
button.addEventListener("click", user.greet.bind(user));

Summary table:
• obj.method()        → this = obj
• fn()                → undefined (strict)
• new Fn()            → the new object
• fn.call(ctx)        → ctx
• arrow function      → this of the outer scope
TXT),
                        ],
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // MODULE 4
            // -----------------------------------------------------------------
            [
                'title' => 'Module 4: Arrays, Objects and Data Structures',
                'description' => 'Store and transform data like a professional: array methods, objects, destructuring, spread, Map, Set and JSON.',
                'sections' => [
                    [
                        'title' => 'Arrays and Array Methods',
                        'description' => 'Create and modify arrays, and transform data with map, filter, reduce, find, some, every and sort.',
                        'minutes' => 40,
                        'materials' => [
                            $this->text('Array Basics', <<<'TXT'
const fruits = ["apple", "banana", "cherry"];
fruits[0]          // "apple"
fruits.at(-1)      // "cherry" (last element)
fruits.length      // 3

Adding / removing (these MUTATE the array):
fruits.push("kiwi");      // add to end
fruits.pop();             // remove from end
fruits.unshift("mango");  // add to start
fruits.shift();           // remove from start
fruits.splice(1, 1);      // remove 1 element at index 1

Searching:
fruits.includes("apple")   // true
fruits.indexOf("banana")   // 1 (or -1 if not found)

Combining and copying (do NOT mutate):
const all = [...fruits, "lemon"];
const part = fruits.slice(0, 2);
fruits.join(", ")          // "apple, banana, cherry"

Newer non-mutating versions (ES2023): toSorted(), toReversed(), toSpliced(), with(index, value).
TXT),
                            $this->text('map, filter, reduce and Friends', <<<'TXT'
const students = [
  { name: "Ana", grade: 9, city: "Prishtina" },
  { name: "Blend", grade: 6, city: "Tirana" },
  { name: "Era", grade: 10, city: "Prishtina" },
];

map — transform every element (same length):
students.map((s) => s.name);          // ["Ana", "Blend", "Era"]

filter — keep elements that pass a test:
students.filter((s) => s.grade >= 8); // Ana, Era

reduce — combine everything into one value:
const total = students.reduce((sum, s) => sum + s.grade, 0);   // 25
const average = total / students.length;

find / findIndex — first element that matches:
students.find((s) => s.city === "Tirana");   // Blend

some / every — boolean tests:
students.some((s) => s.grade === 10);   // true
students.every((s) => s.grade > 5);     // true

sort — mutates! Always pass a compare function for numbers:
[1, 10, 5, 100].sort();             // [1, 10, 100, 5] — sorted as STRINGS!
[1, 10, 5, 100].sort((a, b) => a - b); // [1, 5, 10, 100]
students.toSorted((a, b) => b.grade - a.grade);   // new array, best first

Chaining:
const topNames = students
  .filter((s) => s.grade >= 8)
  .map((s) => s.name.toUpperCase());   // ["ANA", "ERA"]

forEach just runs a function for every element and returns undefined — use it for side effects (logging, DOM updates), not for building new arrays.
TXT),
                            $this->file('Exercises: Array Methods (with solutions)', 'array-methods-exercises.js', '8 exercises on a real product inventory: map, filter, reduce, find, some, grouping, sorting and immutable updates. Run with: node array-methods-exercises.js'),
                        ],
                    ],
                    [
                        'title' => 'Objects, Destructuring and Spread',
                        'description' => 'Model real-world data with objects, access and update properties, and use modern destructuring and spread syntax.',
                        'minutes' => 35,
                        'materials' => [
                            $this->text('Working with Objects', <<<'TXT'
const course = {
  title: "JavaScript",
  level: "beginner",
  modules: 7,
  "created-by": "LM Academy",       // keys with special characters need quotes
  describe() {
    return `${this.title} (${this.level})`;
  },
};

Access:
course.title               // dot notation
course["created-by"]       // bracket notation
const key = "level";
course[key]                // dynamic key

Add / update / delete:
course.price = 0;
course.level = "all levels";
delete course.price;

Check for a property:
"title" in course          // true
Object.hasOwn(course, "title")

Iterate:
Object.keys(course)        // ["title", "level", ...]
Object.values(course)
Object.entries(course).forEach(([key, value]) => console.log(key, value));

Shorthand properties and computed keys:
const name = "Ana", age = 25;
const user = { name, age };                 // { name: "Ana", age: 25 }
const field = "email";
const form = { [field]: "ana@mail.com" };   // { email: "ana@mail.com" }
TXT),
                            $this->text('Destructuring and Spread / Rest', <<<'TXT'
Object destructuring:
const user = { name: "Ana", age: 25, address: { city: "Prizren" } };
const { name, age } = user;
const { name: fullName } = user;               // rename
const { role = "student" } = user;             // default value
const { address: { city } } = user;            // nested

Array destructuring:
const [first, second] = ["gold", "silver", "bronze"];
const [winner, ...others] = ["gold", "silver", "bronze"];
let a = 1, b = 2;
[a, b] = [b, a];                               // swap

Spread ... — expand into a new array/object:
const nums = [1, 2, 3];
const more = [0, ...nums, 4];                  // [0, 1, 2, 3, 4]
Math.max(...nums);                             // 3

const updated = { ...user, age: 26 };          // copy + override
const merged = { ...defaults, ...options };    // later keys win

Shallow vs deep copy:
Spread copies only the first level — nested objects are still shared.
const copy = { ...user };
copy.address.city = "Peja";      // also changes user.address.city!
const deepCopy = structuredClone(user);   // true deep copy
TXT),
                        ],
                    ],
                    [
                        'title' => 'Map, Set and JSON',
                        'description' => 'Specialized collections for unique values and key-value pairs, and converting data to and from JSON.',
                        'minutes' => 25,
                        'materials' => [
                            $this->text('Map and Set', <<<'TXT'
Set — a collection of UNIQUE values:
const tags = new Set(["js", "css", "js", "html"]);
tags.size           // 3
tags.add("vue");
tags.has("css");    // true
tags.delete("html");

Remove duplicates from an array in one line:
const unique = [...new Set([1, 2, 2, 3, 3, 3])];   // [1, 2, 3]

Map — key-value pairs where keys can be ANY type and order is preserved:
const visits = new Map();
visits.set("/home", 10);
visits.set("/about", 3);
visits.get("/home");          // 10
visits.has("/contact");       // false
visits.size                   // 2

for (const [page, count] of visits) {
  console.log(`${page}: ${count}`);
}

When to use Map instead of a plain object:
• Keys are not strings (objects, numbers, DOM elements)
• You add/remove keys frequently
• You need the size or guaranteed insertion order
TXT),
                            $this->text('JSON: Talking to APIs', <<<'TXT'
JSON (JavaScript Object Notation) is the text format used by almost every web API.

Rules: keys must be in double quotes, strings in double quotes, no functions, no undefined, no trailing commas, no comments.

{
  "id": 1,
  "name": "Ana",
  "skills": ["HTML", "CSS", "JavaScript"],
  "active": true
}

Object → JSON string:
const json = JSON.stringify(user);
const pretty = JSON.stringify(user, null, 2);   // indented

JSON string → object:
const data = JSON.parse('{"id":1,"name":"Ana"}');
data.name   // "Ana"

JSON.parse throws on invalid input, so wrap untrusted data:
try {
  const settings = JSON.parse(text);
} catch (err) {
  console.error("Invalid JSON", err.message);
}

Saving data in the browser with localStorage (it only stores strings):
localStorage.setItem("cart", JSON.stringify(cart));
const cart = JSON.parse(localStorage.getItem("cart")) ?? [];
TXT),
                        ],
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // MODULE 5
            // -----------------------------------------------------------------
            [
                'title' => 'Module 5: The DOM and Browser Events',
                'description' => 'Make web pages interactive: select and change elements, create content dynamically, respond to user events and handle forms. Ends with a To-Do app project.',
                'sections' => [
                    [
                        'title' => 'Selecting and Manipulating the DOM',
                        'description' => 'Understand the DOM tree, find elements and change their text, attributes, classes and styles.',
                        'minutes' => 35,
                        'materials' => [
                            $this->image('Diagram: The DOM Tree', 'js-dom-tree.svg', 'The browser turns HTML into a tree of objects (nodes) that JavaScript can read and change.'),
                            $this->text('Finding Elements', <<<'TXT'
The DOM (Document Object Model) is the browser's live, object representation of the HTML page. Changing the DOM changes what the user sees.

Modern selectors (use CSS selector syntax):
document.querySelector("#main")          // first match or null
document.querySelector(".card")
document.querySelector("ul > li:first-child")
document.querySelectorAll(".card")       // NodeList of all matches

Older but still common:
document.getElementById("main")
document.getElementsByClassName("card")  // live HTMLCollection

Traversing:
el.parentElement
el.children
el.firstElementChild / el.lastElementChild
el.nextElementSibling / el.previousElementSibling
el.closest(".card")        // nearest ancestor that matches (or itself)

A NodeList supports forEach; convert to a real array with [...nodeList] or Array.from(nodeList) to use map/filter.
TXT),
                            $this->text('Changing Content, Attributes and Styles', <<<'TXT'
Text and HTML:
el.textContent = "Safe plain text";
el.innerHTML = "<strong>Bold</strong>";
Security warning: never put user input into innerHTML — it allows XSS attacks. Use textContent for user data.

Attributes:
img.src = "photo.jpg";
link.setAttribute("href", "/about");
input.getAttribute("placeholder");
btn.disabled = true;
el.dataset.userId = "42";      // <div data-user-id="42">

Classes (preferred way to change styling):
el.classList.add("active");
el.classList.remove("hidden");
el.classList.toggle("dark-mode");
el.classList.contains("active");

Inline styles (for dynamic values):
box.style.width = `${percent}%`;
box.style.backgroundColor = "tomato";

Creating and removing elements:
const li = document.createElement("li");
li.textContent = "New item";
li.classList.add("item");
list.append(li);          // add as last child
list.prepend(li);         // add as first child
li.remove();

Performance tip: when adding many elements, build them in a DocumentFragment and append once.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Events and Event Delegation',
                        'description' => 'Respond to clicks, key presses and more; understand the event object, bubbling and delegation.',
                        'minutes' => 35,
                        'materials' => [
                            $this->text('Listening to Events', <<<'TXT'
const button = document.querySelector("#save");

button.addEventListener("click", (event) => {
  console.log("Clicked!", event.target);
});

Common events:
• Mouse: click, dblclick, mouseenter, mouseleave
• Keyboard: keydown, keyup  (event.key tells you which key: "Enter", "Escape", "a")
• Forms: submit, input, change, focus, blur
• Window/document: DOMContentLoaded, load, scroll, resize

The event object:
event.target          // the element that was actually clicked
event.currentTarget   // the element the listener is attached to
event.preventDefault() // stop the default browser action (submit, link navigation)
event.stopPropagation() // stop bubbling (use rarely)

Removing a listener requires a reference to the same function:
function onScroll() { ... }
window.addEventListener("scroll", onScroll);
window.removeEventListener("scroll", onScroll);

Listen only once:
btn.addEventListener("click", handler, { once: true });

Keyboard example:
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeModal();
});
TXT),
                            $this->text('Bubbling and Event Delegation', <<<'TXT'
When you click an element, the event first reaches that element and then BUBBLES up through every ancestor: li → ul → body → html → document.

Event delegation uses bubbling: instead of adding a listener to every item, add ONE listener to the parent and check which child was clicked.

<ul id="menu">
  <li data-page="home">Home</li>
  <li data-page="about">About</li>
  <li data-page="contact">Contact</li>
</ul>

const menu = document.querySelector("#menu");
menu.addEventListener("click", (event) => {
  const item = event.target.closest("li");
  if (!item) return;                       // clicked on empty space
  console.log("Go to", item.dataset.page);
});

Benefits:
• One listener instead of hundreds → better performance
• Works automatically for items added LATER (dynamic lists)
• Less code to clean up
TXT),
                        ],
                    ],
                    [
                        'title' => 'Forms and Project: To-Do App',
                        'description' => 'Read and validate form input, then build a complete To-Do application with filtering and localStorage.',
                        'minutes' => 60,
                        'materials' => [
                            $this->text('Handling Forms', <<<'TXT'
<form id="signup">
  <input name="email" type="email" required>
  <input name="password" type="password" minlength="8" required>
  <button>Sign up</button>
  <p id="error"></p>
</form>

const form = document.querySelector("#signup");
const error = document.querySelector("#error");

form.addEventListener("submit", (event) => {
  event.preventDefault();                     // stop page reload

  const data = Object.fromEntries(new FormData(form));
  // data = { email: "...", password: "..." }

  if (!data.email.includes("@")) {
    error.textContent = "Please enter a valid email.";
    return;
  }
  if (data.password.length < 8) {
    error.textContent = "Password must be at least 8 characters.";
    return;
  }

  error.textContent = "";
  console.log("Submitting", data);
  form.reset();
});

Live validation while typing:
input.addEventListener("input", (e) => {
  counter.textContent = `${e.target.value.length}/280`;
});

Remember: client-side validation improves user experience, but the server must ALWAYS validate again.
TXT),
                            $this->text('Project Walkthrough: To-Do App', <<<'TXT'
Download the starter file below and complete the tasks inside the <script> tag.

Recommended approach — keep the DATA as the single source of truth and re-render:

let todos = JSON.parse(localStorage.getItem("todos")) ?? [];

function save() {
  localStorage.setItem("todos", JSON.stringify(todos));
}

function addTodo(text) {
  todos.push({ id: Date.now(), text, done: false });
  save();
  render();
}

function toggleTodo(id) {
  todos = todos.map((t) => (t.id === id ? { ...t, done: !t.done } : t));
  save();
  render();
}

function deleteTodo(id) {
  todos = todos.filter((t) => t.id !== id);
  save();
  render();
}

function render() {
  list.innerHTML = "";
  const visible = todos.filter((t) =>
    currentFilter === "all" ? true : currentFilter === "done" ? t.done : !t.done
  );
  for (const todo of visible) {
    const li = document.createElement("li");
    li.dataset.id = todo.id;
    li.classList.toggle("done", todo.done);
    li.innerHTML = `<input type="checkbox" ${todo.done ? "checked" : ""}>
                    <span></span><button class="delete">Delete</button>`;
    li.querySelector("span").textContent = todo.text;   // safe from XSS
    list.append(li);
  }
  const left = todos.filter((t) => !t.done).length;
  counter.textContent = `${left} item${left === 1 ? "" : "s"} left`;
}

This "state → render" pattern is exactly the idea behind frameworks like Vue and React.
TXT),
                            $this->file('To-Do App Starter Project', 'todo-app-starter.html', 'Complete HTML + CSS starter for the To-Do app with the JavaScript tasks described in comments. Save the file, open it in your browser and implement the TODOs.'),
                        ],
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // MODULE 6
            // -----------------------------------------------------------------
            [
                'title' => 'Module 6: Asynchronous JavaScript',
                'description' => 'Learn how JavaScript handles time-consuming work without freezing: the event loop, callbacks, Promises, async/await and fetching data from real APIs.',
                'sections' => [
                    [
                        'title' => 'The Event Loop and Callbacks',
                        'description' => 'Why JavaScript is single-threaded but non-blocking, and how callbacks, timers and the event loop work together.',
                        'minutes' => 30,
                        'materials' => [
                            $this->text('Synchronous vs Asynchronous Code', <<<'TXT'
Synchronous code runs line by line; each line waits for the previous one.
If one line takes 5 seconds (for example a network request), the whole page would freeze.

Asynchronous code starts a task, continues with other work, and handles the result LATER.

console.log("1. Order coffee");
setTimeout(() => {
  console.log("3. Coffee is ready");
}, 2000);
console.log("2. Read emails while waiting");

Output: 1, 2, 3 — the timer does not block the rest of the code.

Callbacks — a function passed to be called later:
function loadScript(src, callback) {
  const script = document.createElement("script");
  script.src = src;
  script.onload = () => callback(null, script);
  script.onerror = () => callback(new Error(`Failed to load ${src}`));
  document.head.append(script);
}

Callback hell — nested callbacks quickly become unreadable:
getUser(id, (user) => {
  getOrders(user, (orders) => {
    getDetails(orders[0], (details) => {
      // ...and deeper
    });
  });
});
Promises (next section) were invented to solve this.
TXT),
                            $this->image('Diagram: The Event Loop', 'js-event-loop.svg', 'Call stack, Web APIs, the microtask queue and the task queue.'),
                            $this->text('How the Event Loop Works', <<<'TXT'
1. Call stack — where JavaScript executes functions, one at a time.
2. Web APIs — the browser (or Node.js) handles timers, network requests and events outside the JS thread.
3. When they finish, their callbacks are placed in a queue:
   • Microtask queue — Promise callbacks (.then, await), queueMicrotask
   • Task queue — setTimeout, setInterval, DOM events
4. The event loop: whenever the call stack is empty, it runs ALL microtasks, then ONE task, then repeats (and the browser re-renders in between).

Test yourself:
console.log("A");
setTimeout(() => console.log("B"), 0);
Promise.resolve().then(() => console.log("C"));
console.log("D");

Answer: A, D, C, B
• A and D are synchronous.
• C is a microtask — it runs as soon as the stack is empty.
• B is a task — it waits for the microtasks, even with a 0 ms delay.

Consequence: a long synchronous loop blocks everything — clicks, rendering and timers. Keep heavy work small or move it to a Web Worker.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Promises',
                        'description' => 'Create and consume Promises, chain them, handle errors and run multiple async operations in parallel.',
                        'minutes' => 35,
                        'materials' => [
                            $this->image('Diagram: Promise States', 'js-promise-states.svg', 'A promise starts pending and settles exactly once: fulfilled or rejected.'),
                            $this->text('Creating and Consuming Promises', <<<'TXT'
A Promise is an object representing a value that will be available in the future.

Creating one:
function wait(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function rollDice() {
  return new Promise((resolve, reject) => {
    const value = Math.floor(Math.random() * 6) + 1;
    if (value === 1) reject(new Error("You rolled a 1, you lose!"));
    else resolve(value);
  });
}

Consuming it:
rollDice()
  .then((value) => {
    console.log("Rolled", value);
    return value * 2;              // returned value goes to the next .then
  })
  .then((double) => console.log("Double:", double))
  .catch((err) => console.error(err.message))   // catches errors from ANY step above
  .finally(() => console.log("Game over"));

Chaining flattens callback hell:
getUser(id)
  .then((user) => getOrders(user))
  .then((orders) => getDetails(orders[0]))
  .then(showDetails)
  .catch(showError);
TXT),
                            $this->text('Promise Combinators', <<<'TXT'
Promise.all — wait for ALL; rejects as soon as one fails:
const [user, posts] = await Promise.all([fetchUser(1), fetchPosts(1)]);

Promise.allSettled — wait for all, never rejects; gives the status of each:
const results = await Promise.allSettled([p1, p2, p3]);
results.forEach((r) => console.log(r.status, r.value ?? r.reason));

Promise.race — settles with the FIRST promise that settles (useful for timeouts):
const timeout = new Promise((_, reject) =>
  setTimeout(() => reject(new Error("Timeout")), 5000)
);
await Promise.race([fetch(url), timeout]);

Promise.any — resolves with the first SUCCESSFUL promise.

Sequential vs parallel:
// Slow — 3 seconds total
await wait(1000); await wait(1000); await wait(1000);
// Fast — 1 second total
await Promise.all([wait(1000), wait(1000), wait(1000)]);
TXT),
                        ],
                    ],
                    [
                        'title' => 'async/await and the Fetch API',
                        'description' => 'Write asynchronous code that reads like synchronous code, and load real data from REST APIs.',
                        'minutes' => 40,
                        'materials' => [
                            $this->text('async / await', <<<'TXT'
async functions always return a Promise. Inside them, await pauses until a Promise settles and gives you its value.

async function showDice() {
  try {
    const value = await rollDice();
    console.log("Rolled", value);
  } catch (err) {
    console.error(err.message);
  } finally {
    console.log("Done");
  }
}

Same logic as .then/.catch — but much easier to read, especially with conditions and loops.

Arrow version:
const load = async () => { ... };

Common mistakes:
1. Forgetting await → you get a Promise instead of the data.
2. Using await inside forEach — it does NOT wait. Use for...of or Promise.all:
   for (const id of ids) { await save(id); }          // sequential
   await Promise.all(ids.map((id) => save(id)));        // parallel
3. Not handling errors → "Unhandled promise rejection". Always use try/catch (or .catch()).

Top-level await works in ES modules (<script type="module">).
TXT),
                            $this->text('Fetching Data from an API', <<<'TXT'
fetch() makes HTTP requests and returns a Promise of a Response.

GET request:
async function getUsers() {
  const response = await fetch("https://jsonplaceholder.typicode.com/users");

  if (!response.ok) {                          // fetch does NOT reject on 404/500!
    throw new Error(`HTTP error ${response.status}`);
  }

  const users = await response.json();         // parse JSON body (also async)
  return users;
}

Rendering the result with loading and error states:
async function init() {
  list.textContent = "Loading...";
  try {
    const users = await getUsers();
    list.innerHTML = "";
    users.forEach((u) => {
      const li = document.createElement("li");
      li.textContent = `${u.name} — ${u.email}`;
      list.append(li);
    });
  } catch (err) {
    list.textContent = "Could not load users. Please try again.";
  }
}

POST request (sending JSON):
const response = await fetch("https://jsonplaceholder.typicode.com/posts", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({ title: "Hello", body: "My first post", userId: 1 }),
});
const created = await response.json();   // { id: 101, ... }

Authenticated APIs usually expect a header like:
headers: { Authorization: `Bearer ${token}` }
TXT),
                            $this->file('Exercises: Async JavaScript (with solutions)', 'async-exercises.js', '7 exercises: event loop order, wait(), fetching users and posts from a real API, Promise.all, timeouts with Promise.race and a retry helper. Requires Node.js 18+. Run with: node async-exercises.js'),
                        ],
                    ],
                ],
            ],

            // -----------------------------------------------------------------
            // MODULE 7
            // -----------------------------------------------------------------
            [
                'title' => 'Module 7: Modern JavaScript and Best Practices',
                'description' => 'Organize code with ES modules and classes, handle errors properly, debug efficiently, and finish the course by building a Weather Dashboard.',
                'sections' => [
                    [
                        'title' => 'ES Modules',
                        'description' => 'Split your code into reusable files with import and export.',
                        'minutes' => 25,
                        'materials' => [
                            $this->text('import and export', <<<'TXT'
Modules let you split code into files, each with its own scope (no more global variables clashing).

Named exports — many per file:
// math.js
export const PI = 3.14159;
export function area(r) {
  return PI * r * r;
}

// main.js
import { PI, area } from "./math.js";
import { area as circleArea } from "./math.js";   // rename
import * as math from "./math.js";                 // namespace: math.area(2)

Default export — one per file:
// Cart.js
export default class Cart { ... }

// main.js
import Cart from "./Cart.js";          // any name you like

In the browser:
<script type="module" src="main.js"></script>
• Modules are deferred and run in strict mode automatically.
• File extensions (.js) are required in browser imports.
• They must be served over HTTP (Live Server, npx serve) — not opened as file://.

Dynamic import — load code only when needed:
button.addEventListener("click", async () => {
  const { openEditor } = await import("./editor.js");
  openEditor();
});

In Node.js, use the .mjs extension or add "type": "module" to package.json.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Classes and Prototypes',
                        'description' => 'Object-oriented JavaScript: classes, constructors, inheritance, getters/setters, static and private members.',
                        'minutes' => 35,
                        'materials' => [
                            $this->text('Classes', <<<'TXT'
class BankAccount {
  #balance = 0;                    // private field — not accessible outside
  static bankName = "LM Bank";     // belongs to the class, not instances

  constructor(owner) {
    this.owner = owner;
  }

  deposit(amount) {
    if (amount <= 0) throw new Error("Amount must be positive");
    this.#balance += amount;
    return this;                   // allows chaining
  }

  get balance() {                  // getter — used like a property
    return this.#balance;
  }

  static compare(a, b) {
    return a.balance - b.balance;
  }
}

const acc = new BankAccount("Ana");
acc.deposit(100).deposit(50);
acc.balance          // 150
acc.#balance         // SyntaxError — private!
BankAccount.bankName // "LM Bank"

Inheritance:
class SavingsAccount extends BankAccount {
  constructor(owner, rate) {
    super(owner);                  // must call super() before using this
    this.rate = rate;
  }
  addInterest() {
    return this.deposit(this.balance * this.rate);
  }
}
TXT),
                            $this->text('Prototypes Under the Hood', <<<'TXT'
Classes are "syntactic sugar" over prototypes. Every object has a hidden link [[Prototype]] to another object. When a property is not found on the object, JavaScript looks it up on the prototype, then on the prototype's prototype, and so on (the prototype chain).

const arr = [1, 2, 3];
arr.map   // not on arr itself → found on Array.prototype
Object.getPrototypeOf(arr) === Array.prototype                  // true
Object.getPrototypeOf(Array.prototype) === Object.prototype     // true

That is why all arrays share the same methods without copying them.

The same class written with a constructor function (pre-ES6 style):
function Person(name) {
  this.name = name;
}
Person.prototype.greet = function () {
  return `Hi, I am ${this.name}`;
};
const p = new Person("Blend");
p.greet();

What "new" does:
1. Creates an empty object
2. Links its prototype to Person.prototype
3. Runs the function with this = the new object
4. Returns the object

Tip: prefer composition (small objects/functions combined together) over deep inheritance chains.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Error Handling and Debugging',
                        'description' => 'Throw and catch errors, create custom error types and debug efficiently with DevTools.',
                        'minutes' => 30,
                        'materials' => [
                            $this->text('Handling Errors', <<<'TXT'
Built-in error types: Error, TypeError, ReferenceError, SyntaxError, RangeError.

try {
  const data = JSON.parse(input);
  processOrder(data);
} catch (err) {
  console.error(err.name, err.message);
  showMessage("Something went wrong, please try again.");
} finally {
  hideSpinner();       // always runs
}

Throwing your own errors:
function divide(a, b) {
  if (b === 0) throw new RangeError("Cannot divide by zero");
  return a / b;
}

Custom error classes:
class ValidationError extends Error {
  constructor(field, message) {
    super(message);
    this.name = "ValidationError";
    this.field = field;
  }
}

try {
  throw new ValidationError("email", "Email is required");
} catch (err) {
  if (err instanceof ValidationError) {
    highlightField(err.field);
  } else {
    throw err;          // re-throw errors you cannot handle
  }
}

Global safety nets (for logging):
window.addEventListener("error", (e) => report(e.error));
window.addEventListener("unhandledrejection", (e) => report(e.reason));
TXT),
                            $this->text('Debugging Like a Pro', <<<'TXT'
1. Read the error message and stack trace — it tells you the file and line number.
   "Cannot read properties of undefined (reading 'name')" → you accessed .name on something that is undefined. Find out why.

2. Console tools:
console.log({ user, total });      // wrap in {} to see variable names
console.table(users);
console.group("Checkout"); ... console.groupEnd();
console.time("sort"); data.sort(); console.timeEnd("sort");
console.trace();                   // who called this function?

3. Breakpoints in Chrome DevTools (Sources tab):
• Click a line number to pause there.
• Or write the statement debugger; in your code.
• Step over (F10), step into (F11), step out (Shift + F11).
• Inspect variables in the Scope panel and hover over values.
• Conditional breakpoints: right-click a line number → "Add conditional breakpoint".

4. Network tab — inspect every fetch request: URL, status code, request/response body.

5. Prevent bugs before they happen:
• Use const, === and strict mode
• ESLint + Prettier in VS Code
• Small, pure functions that are easy to test
• Consider TypeScript for larger projects — it catches type errors before the code runs.
TXT),
                        ],
                    ],
                    [
                        'title' => 'Final Project: Weather Dashboard',
                        'description' => 'Apply everything you have learned by building a complete weather application with a real API.',
                        'minutes' => 120,
                        'materials' => [
                            $this->text('Project Overview', <<<'TXT'
You will build a Weather Dashboard with vanilla JavaScript using the free Open-Meteo API (no API key needed).

Skills you will practice:
• DOM manipulation and event handling (Module 5)
• async/await, fetch, Promise.all and error handling (Module 6)
• ES modules and clean project structure (Module 7)
• Arrays/objects to transform API data (Module 4)
• localStorage for recent searches

Step-by-step plan:
1. Create the HTML layout: search form, current weather card, forecast list, recent searches.
2. api.js — write getCoordinates(city) using the geocoding endpoint; throw an error if no results.
3. api.js — write getWeather(lat, lon) using the forecast endpoint.
4. ui.js — render functions that take data and build DOM elements (no fetch calls here!).
5. main.js — on submit: show loading → await data → render → save to recent searches; catch errors and show them nicely.
6. Add the °C / °F toggle by re-rendering stored data.
7. Polish: empty states, disabled button while loading, responsive CSS.

Starter code for api.js:
const GEO_URL = "https://geocoding-api.open-meteo.com/v1/search";
const WEATHER_URL = "https://api.open-meteo.com/v1/forecast";

export async function getCoordinates(city) {
  const res = await fetch(`${GEO_URL}?name=${encodeURIComponent(city)}&count=1`);
  if (!res.ok) throw new Error("Geocoding request failed");
  const data = await res.json();
  if (!data.results?.length) throw new Error(`City "${city}" not found`);
  const { latitude, longitude, name, country } = data.results[0];
  return { latitude, longitude, name, country };
}

Download the full project brief below for requirements, file structure and the grading rubric. Good luck — and congratulations on reaching the end of the course!
TXT),
                            $this->file('Final Project Brief & Grading Rubric', 'final-project-brief.html', 'Full requirements, API endpoints, suggested module structure, grading rubric (100 points) and bonus ideas for the Weather Dashboard project.'),
                            $this->text('What to Learn Next', <<<'TXT'
You now know the core of JavaScript. Suggested next steps:

1. TypeScript — JavaScript with static types; standard in most professional teams.
2. A frontend framework — Vue.js (see our "Vue.js Advance" course), React or Svelte.
3. Node.js and Express / NestJS — build your own REST APIs with JavaScript.
4. Testing — Vitest or Jest for unit tests, Playwright for end-to-end tests.
5. Tooling — npm, Vite, Git and GitHub.

Great free resources:
• MDN Web Docs — https://developer.mozilla.org/en-US/docs/Web/JavaScript
• javascript.info — https://javascript.info
• Eloquent JavaScript (free online book) — https://eloquentjavascript.net
• Practice challenges — https://www.codewars.com and https://exercism.org/tracks/javascript

Keep building small projects — that is the fastest way to become a confident developer.
TXT),
                        ],
                    ],
                ],
            ],
        ];
    }
}
