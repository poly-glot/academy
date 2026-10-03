FROM europe-west2-docker.pkg.dev/firebase-cloud-491613/firebase-cloud/wp-base:7.1-r2

COPY wp-content /app/public/wp-content

RUN cd /app/public/wp-content/themes/academy/assets/css \
 && cat base/tokens.css base/base.css base/layout.css atoms/button.css atoms/icon.css atoms/kicker.css atoms/logo.css atoms/meta.css atoms/prose.css components/site-header.css components/site-footer.css components/hero.css components/page-hero.css components/band.css components/feature-list.css components/value-grid.css components/goals.css components/tabs.css components/person-card.css components/level-card.css components/fee-card.css components/steps.css components/course-list.css components/news-card.css components/article.css components/facts.css components/subnav.css components/award-strip.css components/credential.css components/form.css components/login.css components/contact.css components/community.css > main.css

RUN wp-build
