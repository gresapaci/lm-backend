/**
 * Arrays & Objects — Exercises
 * Course: Complete JavaScript: From Fundamentals to Modern JS
 *
 * Run with:  node array-methods-exercises.js
 * Rule: solve every exercise WITHOUT for/while loops — use map, filter,
 * reduce, find, some, every, sort, Object.entries, etc.
 */

const products = [
  { id: 1, name: "Laptop", category: "electronics", price: 1200, stock: 5 },
  { id: 2, name: "Headphones", category: "electronics", price: 150, stock: 0 },
  { id: 3, name: "Coffee Mug", category: "kitchen", price: 12, stock: 40 },
  { id: 4, name: "Desk Lamp", category: "home", price: 45, stock: 12 },
  { id: 5, name: "Keyboard", category: "electronics", price: 80, stock: 7 },
  { id: 6, name: "Frying Pan", category: "kitchen", price: 35, stock: 0 },
];

// 1. Return an array with only the product names.
const getNames = (list) => {
  // TODO
};

// 2. Return only the products that are in stock (stock > 0).
const inStock = (list) => {
  // TODO
};

// 3. Return the total value of the inventory (price * stock for every product).
const inventoryValue = (list) => {
  // TODO
};

// 4. Return the product with the given id, or undefined.
const findById = (list, id) => {
  // TODO
};

// 5. Return true if ANY product costs more than `limit`.
const hasExpensive = (list, limit) => {
  // TODO
};

// 6. Group products by category:
//    { electronics: ["Laptop", ...], kitchen: [...], home: [...] }
const groupByCategory = (list) => {
  // TODO
};

// 7. Return a NEW array sorted by price ascending (do not mutate the original).
const sortByPrice = (list) => {
  // TODO
};

// 8. Apply a discount (percent) to every product and return new objects:
//    { ...product, price: discountedPrice } — round to 2 decimals.
const applyDiscount = (list, percent) => {
  // TODO
};

// ---------------------------------------------------------------------------
// Checks — do not edit below this line
// ---------------------------------------------------------------------------
function check(label, actual, expected) {
  const ok = JSON.stringify(actual) === JSON.stringify(expected);
  console.log(`${ok ? "PASS" : "FAIL"}  ${label}`);
  if (!ok) console.log("   got:", actual, "\n   expected:", expected);
}

check("getNames", getNames(products), ["Laptop", "Headphones", "Coffee Mug", "Desk Lamp", "Keyboard", "Frying Pan"]);
check("inStock", inStock(products)?.map((p) => p.id), [1, 3, 4, 5]);
check("inventoryValue", inventoryValue(products), 7580);
check("findById", findById(products, 4)?.name, "Desk Lamp");
check("hasExpensive(1000)", hasExpensive(products, 1000), true);
check("hasExpensive(5000)", hasExpensive(products, 5000), false);
check("groupByCategory", groupByCategory(products), {
  electronics: ["Laptop", "Headphones", "Keyboard"],
  kitchen: ["Coffee Mug", "Frying Pan"],
  home: ["Desk Lamp"],
});
check("sortByPrice", sortByPrice(products)?.map((p) => p.price), [12, 35, 45, 80, 150, 1200]);
check("sortByPrice does not mutate", products[0].name, "Laptop");
check("applyDiscount", applyDiscount(products, 10)?.[2].price, 10.8);

/* ---------------------------------------------------------------------------
   SOLUTIONS
   ---------------------------------------------------------------------------
const getNames = (list) => list.map((p) => p.name);
const inStock = (list) => list.filter((p) => p.stock > 0);
const inventoryValue = (list) => list.reduce((sum, p) => sum + p.price * p.stock, 0);
const findById = (list, id) => list.find((p) => p.id === id);
const hasExpensive = (list, limit) => list.some((p) => p.price > limit);
const groupByCategory = (list) =>
  list.reduce((groups, p) => {
    (groups[p.category] ??= []).push(p.name);
    return groups;
  }, {});
const sortByPrice = (list) => [...list].sort((a, b) => a.price - b.price);
const applyDiscount = (list, percent) =>
  list.map((p) => ({ ...p, price: Math.round(p.price * (1 - percent / 100) * 100) / 100 }));
*/
