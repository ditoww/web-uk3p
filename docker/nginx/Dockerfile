# Stage 1: Build Vite frontend assets
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json vite.config.js ./
RUN npm ci || npm install
COPY resources/ ./resources/
COPY public/ ./public/
RUN npm run build

# Stage 2: Nginx Web Server
FROM nginx:alpine
WORKDIR /var/www

# Copy Nginx server block config
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy public static assets and compiled Vite build
COPY public ./public
COPY --from=frontend-builder /app/public/build ./public/build

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]

