const colors = require('tailwindcss/colors')

module.exports = {
	purge: [],
  darkMode: false, // or 'media' or 'class'
  theme: {
    fontFamily: {
      display: ['Roboto', 'system-ui', 'sans-serif'],
      body: ['Roboto', 'system-ui', 'sans-serif'],
    },
  	extend: {
  		colors: {
  			bluegray: colors.blueGray,
  			lightblue: colors.lightBlue,
  		}
    }
  },
  variants: {
  	 extend: {
      textColor: ['group-focus', 'disabled'],
      opacity: ['disabled'],
      backgroundColor: ['disabled'],
      cursor: ['disabled']
    }
  },
  plugins: [],
}
