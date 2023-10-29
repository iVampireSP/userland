# OAuth

OAuth 服务器部署

## 1. 创建 Secret（如果有必要的话）

```bash
kubectl create namespace ecosystem

kubectl create --namespace ecosystem secret docker-registry    \
  --docker-server=registry.daisukide.com:2083 \
  --docker-username=<用户名> \
  --docker-password=<密码>
```

## 2. 执行部署

```bash
kubectl apply -f manifest.yaml
```

## 3. 给 Ecosystem 的 default 绑定 RoleBinding。可以操作 Ecosystem 命名空间中的所有资源

```bash
kubectl create rolebinding default-admin --clusterrole=admin --serviceaccount=ecosystem:default --namespace=ecosystem
```

## 4. 部署 Gitlab Runner
```bash

```