# Optional frontend container template.
# Replace the build context with your frontend repository when ready.
#
# Example for a Vite/React or Next.js static export served by nginx:
#
# FROM node:22-alpine AS build
# WORKDIR /app
# COPY package*.json ./
# RUN npm ci
# COPY . .
# RUN npm run build
#
# FROM nginx:alpine
# COPY --from=build /app/dist /usr/share/nginx/html
# COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
# EXPOSE 80

FROM nginx:alpine
COPY nginx.conf /etc/nginx/conf.d/default.conf
RUN mkdir -p /usr/share/nginx/html \
    && printf '%s\n' \
      '<!doctype html><html><head><meta charset="utf-8"><title>Waves Frontend</title></head>' \
      '<body style="font-family:sans-serif;padding:2rem">' \
      '<h1>Frontend placeholder</h1>' \
      '<p>Replace this container with the real Waves frontend build.</p>' \
      '<p>API docs: <a href="http://localhost:8000/api/doc">/api/doc</a></p>' \
      '</body></html>' > /usr/share/nginx/html/index.html
EXPOSE 80
