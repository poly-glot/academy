# Academy

Website for Academy, a fictional professional business-education body in Birmingham, on the
agency WordPress platform (Cloud Run). WordPress core, wp-config, mu-plugins (yacf, GCS
uploads) and the APCu object cache live in the `wp-base` image; this repo owns only what
is unique to the client. All people, courses, prices and copy are invented.

## What you own

| Path | Purpose |
| --- | --- |
| `static/` | Verified static HTML/CSS prototype; the source the theme is ported from |
| `wp-content/themes/` | The `academy` theme |
| `wp-content/plugins/` | Client plugins (rarely needed) |
| `wp-content/yacf/` | Content model: post types, taxonomies, fields, options pages (YAML) |
| `wp-content/seed/` | `seed.php` (idempotent content seed) + its images. `run.php` is the platform's bootstrap runner, never edit it |
| `Dockerfile` | Three steps: copy `wp-content`, bundle the theme CSS, `wp-build`. Bump the `wp-base` tag to take platform updates |
| `firebase.json` | Firebase Hosting rewrite for `academy.junaid.guru` (site `academy-junaid`) |

## Static prototype

`static/` is the verified HTML source of the theme: 26 pages, WCAG 2.2 AA. Every page,
string and image there is final; the WordPress theme and seed reproduce it 1:1. Design
changes are made in `static/` first, then mirrored into the theme (CSS is copied
byte-identical). Serve it with:

```bash
python3 -m http.server 8770 --directory static
```

Then open http://localhost:8770.

The Home page uses the `hero--circle` hero; About and Membership use `hero--bleed`.

## Local development

Open in the devcontainer. It builds the image, starts MariaDB, installs WordPress
(admin / password), builds the yacf model and runs `wp-content/seed/run.php`.
Site: http://localhost:8080

The devcontainer mounts your `wp-content` dirs into the running image and enables
OPcache timestamp validation, so edits apply on refresh. Only one client devcontainer
can run at a time on this host (all map to host ports 8080/3307); stop one before
starting another.

## Onboarding checklist

1. firebase-cloud: `terraform/apps/academy.tf`, the catalog entry in `apps/mysql-catalog.tf`
   and the outputs in `apps/outputs.tf` and the root `outputs.tf`. Run `terraform apply`
   (the first apply is expected to fail creating the Cloud Run service because the DB
   secret versions do not exist yet).
2. personal-cloud: `gh workflow run terraform-mysql-apps.yaml --repo poly-glot/personal-cloud --ref main`
   to provision the database and secret versions, then re-run `terraform apply`.
3. `gh secret set WIF_PROVIDER -R poly-glot/academy --body "$(terraform output -raw academy_wif_provider)"`
   and the same for `GCP_SA_EMAIL` from `academy_gcp_sa_email`.
4. Admin password:
   `openssl rand -base64 30 | tr -d '/+=' | cut -c1-24 | tr -d '\n' | gcloud secrets create academy-wp-admin-pass --project firebase-cloud-491613 --replication-policy=automatic --data-file=-`
5. Create the bootstrap job before the first deploy goes live (the production image has
   no wp-cli; `run.php` installs WordPress and runs the seed), then execute it:
   ```bash
   gcloud run jobs create academy-bootstrap --project firebase-cloud-491613 --region europe-west2 \
     --image europe-west2-docker.pkg.dev/firebase-cloud-491613/firebase-cloud/academy:<tag> \
     --command php --args /app/public/wp-content/seed/run.php \
     --service-account academy-runtime@firebase-cloud-491613.iam.gserviceaccount.com \
     --set-secrets DB_HOST=db-host:latest,DB_USER=academy-db-user:latest,DB_PASS=academy-db-pass:latest,DB_NAME=academy-db-name:latest,WP_ADMIN_PASS=academy-wp-admin-pass:latest \
     --set-env-vars DB_SSL=1,WP_TITLE=Academy,WP_ADMIN_USER=admin,WP_ADMIN_EMAIL=me@junaid.guru,GCS_UPLOADS_BUCKET=firebase-cloud-491613-academy-uploads \
     --max-retries 0 --task-timeout 600
   gcloud run jobs execute academy-bootstrap --project firebase-cloud-491613 --region europe-west2 --wait
   ```
   CI updates and re-executes the job on every push.
6. Push to `main`. The deploy workflow builds, deploys Cloud Run service `academy`, runs
   the bootstrap job and deploys Firebase Hosting.
7. DNS: `CNAME academy -> academy-junaid.web.app` for `academy.junaid.guru`.

## Production

Pushes to `main` deploy to Cloud Run (service `academy`) in `firebase-cloud-491613` /
`europe-west2`, then re-run the bootstrap job so content model and seed changes land.
Firebase Hosting serves `academy.junaid.guru` by rewriting `**` to the service and
forwarding only the `__session` cookie: the public site works, but wp-admin stays on the
run.app URL. Media uploads go to the app's GCS bucket via the runtime service account.
wp-cron runs from Cloud Scheduler; file mods are disabled on Cloud Run.
