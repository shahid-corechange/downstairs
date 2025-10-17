module.exports = {
  "*.{js,ts,jsx,tsx}": "eslint --fix --quiet",
  // Temporarily disabled due to memory issues
  // "*.{ts,tsx}": () => "tsc --pretty --noEmit",
};
