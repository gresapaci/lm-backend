/**
 * JavaScript Fundamentals — Exercises
 * Course: Complete JavaScript: From Fundamentals to Modern JS
 *
 * How to use:
 *   1. Save this file and run it with Node.js:  node fundamentals-exercises.js
 *   2. Implement each function below (replace the "TODO" lines).
 *   3. The checks at the bottom print PASS / FAIL for every exercise.
 */

// 1. Return the type of the value as a string, but return "null" for null
//    and "array" for arrays.  describe(null) -> "null", describe([]) -> "array"
function describe(value) {
  // TODO
}

// 2. Convert a temperature from Celsius to Fahrenheit (F = C * 9/5 + 32).
function toFahrenheit(celsius) {
  // TODO
}

// 3. Return "Fizz" for multiples of 3, "Buzz" for multiples of 5,
//    "FizzBuzz" for multiples of both, otherwise the number itself.
function fizzBuzz(n) {
  // TODO
}

// 4. Return the grade letter: 90+ "A", 80+ "B", 70+ "C", 60+ "D", else "F".
//    Invalid input (not a number, < 0 or > 100) should return "Invalid".
function grade(score) {
  // TODO
}

// 5. Sum all numbers from 1 to n using a loop.
function sumTo(n) {
  // TODO
}

// 6. Return true if the string reads the same backwards (ignore case and spaces).
//    isPalindrome("Never odd or even") -> true
function isPalindrome(text) {
  // TODO
}

// 7. Count the vowels (a, e, i, o, u) in a string, case-insensitive.
function countVowels(text) {
  // TODO
}

// ---------------------------------------------------------------------------
// Checks — do not edit below this line
// ---------------------------------------------------------------------------
function check(label, actual, expected) {
  const ok = JSON.stringify(actual) === JSON.stringify(expected);
  console.log(`${ok ? "PASS" : "FAIL"}  ${label}  ->  got ${JSON.stringify(actual)}, expected ${JSON.stringify(expected)}`);
}

check("describe(null)", describe(null), "null");
check("describe([1])", describe([1]), "array");
check("describe('x')", describe("x"), "string");
check("toFahrenheit(100)", toFahrenheit(100), 212);
check("toFahrenheit(-40)", toFahrenheit(-40), -40);
check("fizzBuzz(9)", fizzBuzz(9), "Fizz");
check("fizzBuzz(10)", fizzBuzz(10), "Buzz");
check("fizzBuzz(30)", fizzBuzz(30), "FizzBuzz");
check("fizzBuzz(7)", fizzBuzz(7), 7);
check("grade(95)", grade(95), "A");
check("grade(61)", grade(61), "D");
check("grade(120)", grade(120), "Invalid");
check("sumTo(100)", sumTo(100), 5050);
check("isPalindrome('Never odd or even')", isPalindrome("Never odd or even"), true);
check("isPalindrome('JavaScript')", isPalindrome("JavaScript"), false);
check("countVowels('JavaScript')", countVowels("JavaScript"), 3);

/* ---------------------------------------------------------------------------
   SOLUTIONS (try on your own first!)
   ---------------------------------------------------------------------------
function describe(value) {
  if (value === null) return "null";
  if (Array.isArray(value)) return "array";
  return typeof value;
}
const toFahrenheit = (c) => c * 9 / 5 + 32;
function fizzBuzz(n) {
  if (n % 15 === 0) return "FizzBuzz";
  if (n % 3 === 0) return "Fizz";
  if (n % 5 === 0) return "Buzz";
  return n;
}
function grade(score) {
  if (typeof score !== "number" || Number.isNaN(score) || score < 0 || score > 100) return "Invalid";
  if (score >= 90) return "A";
  if (score >= 80) return "B";
  if (score >= 70) return "C";
  if (score >= 60) return "D";
  return "F";
}
function sumTo(n) {
  let total = 0;
  for (let i = 1; i <= n; i++) total += i;
  return total;
}
function isPalindrome(text) {
  const clean = text.toLowerCase().replaceAll(" ", "");
  return clean === [...clean].reverse().join("");
}
const countVowels = (text) => [...text.toLowerCase()].filter((ch) => "aeiou".includes(ch)).length;
*/
