// fontawesome.js

import { library, dom } from '@fortawesome/fontawesome-svg-core';
import * as regularIcons from '@fortawesome/free-regular-svg-icons';
import * as brandIcons from '@fortawesome/free-brands-svg-icons';

// For Pro icons (if applicable):
// import * as proSolidIcons from '@fortawesome/pro-solid-svg-icons';
// import * as proRegularIcons from '@fortawesome/pro-regular-svg-icons';
// import * as proLightIcons from '@fortawesome/pro-light-svg-icons';

// Add all icons from each package to the library
Object.keys(solidIcons).forEach((key) => {
  if (key !== 'fas' && key !== 'prefix') {
    library.add(solidIcons[key]);
  }
});

Object.keys(regularIcons).forEach((key) => {
  if (key !== 'far' && key !== 'prefix') {
    library.add(regularIcons[key]);
  }
});

Object.keys(brandIcons).forEach((key) => {
  if (key !== 'fab' && key !== 'prefix') {
    library.add(brandIcons[key]);
  }
});

// For Pro icons (if applicable):
// Object.keys(proSolidIcons).forEach((key) => {
//   if (key !== 'fas' && key !== 'prefix') {
//     library.add(proSolidIcons[key]);
//   }
// });

// Object.keys(proRegularIcons).forEach((key) => {
//   if (key !== 'far' && key !== 'prefix') {
//     library.add(proRegularIcons[key]);
//   }
// });

// Object.keys(proLightIcons).forEach((key) => {
//   if (key !== 'fal' && key !== 'prefix') {
//     library.add(proLightIcons[key]);
//   }
// });

// Watch and replace <i> tags with <svg>
dom.watch();
