# OctoCart Plugin

**OctoCart** is a simple eCommerce plugin for October.

Now you can create products with attributes and categories with unlimited subcategory nesting.

You can visit [Demo site](http://demo.octobercms.info/octocart).

Front-end:

* login: demo@octobercms.info
* password: demo

Backend:

* login: demo
* password: demo123

You can use the [demo theme](https://bitbucket.org/sozonovalexey/oc-octocart-theme) to get started quickly.

If you have some problems or you want more functionality, please make a topic in the support section and I will try to add or modify the plugin functions.

**If you like OctoCart, please leave a review**. Thank you!

### Requirements

* [RainLab.User](http://octobercms.com/plugin/rainlab-user) plugin

## DOCUMENTATION

# Installation
1. Add **OctoCart** plugin to a project. 
2. Change plugin settings on the configuration page: **/backend/system/settings/update/xeor/octocart/settings**.
3. Create some products: **/backend/xeor/octocart/products**.
4. Create categories: **/backend/xeor/octocart/categories**.
5. Create pages: **Product**, **Category**, **Cart**, **Checkout**, **Success**, **Order**.
6. Add plugin components to a your pages.

#Backend
In the back-end user interface you can manage products, categories and orders.

# Components

Name | Page variable | Description 
------------- | ------------- | -------------
Cart | `{% component 'cart' %}` | Show the contents of and process the user's cart
Category List | `{% component 'categories' %}` | Displays a list of categories on the page
Checkout | `{% component 'checkout' %}` |  Displays Checkout form on the page
Order | `{% component 'order' %}` |  Display a single order
Orders | `{% component 'orders' %}` |  Displays a list of orders on the page
Product | `{% component 'product' %}` | Display a single product
Products | `{% component 'products' %}` | Displays a list of products on the page


##Cart Component

**Properties**

Property | Description | Example Value | Default Value
------------- | ------------- | ------------- | -------------
noProductsMessage  | No products message | No products found | No products found

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ cartPage }}`  | link to cart
`{{ checkoutPage }}`  | link to checkout page
`{{ count }}`  | quantity of products in the cart
`{{ items }}`  | product items
`{{ crossSells }}`  | products that you promote in the cart, based on the current product
`{{ noProductsMessage }}`  | no products message
`{{ totalPrice }}`  | total price

**Example:**
~~~
{% if not items is empty %}
    {% for itemId, item in items %}
        {% set product = item.product %}
        {% set quantity = item.quantity %}
        {% set price = item.price %}
        {% set attributes = item.attributes %}
        {% if attributes is not empty %}
            {% for name, value in attributes %}
                {{ name }}: {{ value }}
            {% endfor %}
        {% endif %}
    {% endfor %}
{% else %}
    {{ noProductsMessage }}
{% endif %}
~~~

##Categories Component

**Properties**

Property | Description | Example Value | Default Value
------------- | ------------- | ------------- | -------------
Slug  | Category slug | :slug | :slug
Display empty categories | Display empty categories | TRUE | FALSE

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ categories }}` | all categories
`{{ categoryPage }}` | category page
`{{ currentCategorySlug }}` | reference to the current category slug

**Example:**
~~~
{% for category in categories %}
    {% set productCount = category.product_count %}
    <li {% if category.slug == currentCategorySlug %}class="active"{% endif %}>
        <a href="{{ category.url }}">{{ category.title }}</a>
        {% if productCount %}
            <span class="badge">{{ productCount }}</span>
        {% endif %}

        {% if category.children.count %}
            <ul>
                {% partial __SELF__ ~ "::items"
                    categories=category.children
                    currentCategorySlug=currentCategorySlug
                %}
            </ul>
        {% endif %}
    </li>
{% endfor %}
~~~

##Checkout Component

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ successPage }}` | link to success page

##Order Component

**Properties**

Property | Description | Example Value | Default Value
------------- | ------------- | ------------- | -------------
ID  | Order id | 1 | :id

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ order }}` | order
`{{ items }}` | product items

##Orders Component

**Properties**

Property | Description | Example Value | Default Value
------------- | ------------- | ------------- | -------------
No Orders Message  | No Orders Message | No orders found | No orders found

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ orders }}` | orders
`{{ noOrdersMessage }}` | no orders message

**Example:**
~~~
{% for order in orders %}
    <li>
        <a href="{{ order.url }}">Order №{{ order.id }}</a> - {{ order.created_at|date('M d, Y') }}
    </li>
{% else %}
    <h3 class="text-center">{{ noOrdersMessage }}</h3>
{% endfor %}
~~~

##Product Component

**Properties**

Property | Description | Example Value | Default Value
------------- | ------------- | ------------- | -------------
Slug  | Category slug | :slug | :slug

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ cartPage }}` | link to cart
`{{ categoryPage }}` | link to category
`{{ productDisplayPage }}` | link to category
`{{ product }}` | product
`{{ product.attributes }}` | product attributes
`{{ product.variations }}` | product variations
`{{ upSells }}` | products that you recommend instead of the currently viewed product

##Products Component

**Properties**

Property | Description | Example Value | Default Value
------------- | ------------- | ------------- | -------------
pageNumber | this value is used to determine what page the user is on, it should be a routing parameter for the default markup| 1 | {{ :page }}
categoryFilter | a category slug to filter the posts by | 1 | 
productsPerPage | how many posts to display on a single page (the pagination is supported automatically) | 4 | 10
noProductsMessage | message to display in the empty post list | No products found | No products found
sortOrder | the column name and direction used for the sort order of the products | created_at asc | created_at desc
promote | promoted to front page | true | false

**Variables available in templates**

Variable  | Description 
------------- | -------------
`{{ cartPage }}` | link to cart
`{{ category }}` | category
`{{ noProductsMessage }}` | no products message
`{{ pageParam }}` | current page
`{{ products }}` | products

**Example:**
~~~
title = "Category"
url = "/categories/:slug/:page?"

[products]
pageNumber = "{{ :page }}"
categoryFilter = "{{ :slug }}"
productsPerPage = 4
noProductsMessage = "No products found"
sortOrder = "created_at desc"
categoryPage = "category"
productDisplayPage = "product"
==
...
{% component 'products' %}
...
~~~

##Why is this plugin a paid plugin ?

This plugin requires a lot of time to develop. The actual price would be used for support and future development. Additionally, 30% of your purchase goes to help fund the October Project!