import ReactPDF from '@react-pdf/renderer'
import styles from './styles.js'

const compose = (classes) => {
  const css = {}

  const classesArray = classes.replace(/\s+/g, ' ').split(' ')

  classesArray.forEach((className) => {
    if (typeof styles[className] !== undefined) {
      Object.assign(css, styles[className])
    }
  })

  return css
}

export default compose
