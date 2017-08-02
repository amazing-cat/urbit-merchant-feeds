const copy = require('cpy');
const Metalsmith = require('metalsmith');
const markdown = require('metalsmith-markdownit');
const layouts = require('metalsmith-layouts');
const renamer = require('metalsmith-renamer');

const destination = 'dist';
const md = markdown('default', { html: true, typographer: true });
const handleCriticalError = e => {
  throw e;
};

md.parser.enable(['emphasis', 'html_block']);
md.parser.use(require('markdown-it-emoji'));

Metalsmith(__dirname)
  .metadata({
    sitename: 'Urbit merchandise feeds'
  })
  .source('./')
  .ignore(['node_modules', '!*.md'])
  .destination(destination)
  .clean(true)
  .use(md)
  .use(
    layouts({
      engine: 'handlebars',
      default: 'index.html'
    })
  )
  .use(
    renamer({
      readme: {
        pattern: 'README.html',
        rename: 'index.html'
      }
    })
  )
  .build(err => {
    if (err) {
      handleCriticalError(err);
    }

    copy(['node_modules/github-markdown-css/github-markdown.css'], destination);
  });
