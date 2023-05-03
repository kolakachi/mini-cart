<section class="container mt-4" id="mini-cart">
    <div class="container-fluid mb-4">
        <div class="row">
            <div class="col-lg-12">
                <h4 class="text-capitalize">shop</h4>
                <div class="btn-group" role="group" aria-label="Basic example">
                    <button type="button" class="btn"
                        :disabled="activeTab == 'home'" :class="{'btn-secondary':activeTab == 'home', 'btn-primary':activeTab != 'home'}"
                        @click="setActiveTab('home')">Home</button>
                    <button type="button" class="btn position-relative"
                        :disabled="activeTab == 'cart'"
                        :class="{'btn-secondary':activeTab == 'cart', 'btn-primary':activeTab != 'cart'}" @click="setActiveTab('cart')">Cart
                        <span v-cloak v-show="numberOfItemsInCart != 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{numberOfItemsInCart}}
                            <span class="visually-hidden">unread messages</span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4" v-show="activeTab == 'home' && !pageIsLoading" v-cloak>
        <aside class="col-lg-3">
            <div class="form mb-1">
                <input type="text" class="form-control" v-model="searchQuery" aria-label="Enter product price or product name" placeholder="Product price or product name">
            </div>
            <div class="accordion-body pt-1">

                <div class="row row-cols-md-2 g-2 mb-3">
                    <div class="col">
                        <label>Order By</label>
                        <select v-model="orderBy" class="form-control">
                            <option value="name">Name</option>
                            <option value="price">Price</option>
                        </select>
                    </div>
                    <div class="col text-end">
                        <label>Sort By</label>
                        <select v-model="sortBy" class="form-control">
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </select>
                    </div>
                </div>

            </div>
        </aside>
        <div class="col-lg-9">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 mb-4 pb-4 g-4">

                <div class="col-4" v-for="(product, index) in products">
                    <div class="card h-100">
                        <img class="card-img-top" :src="product.image_url" alt="Placeholder">
                        <div class="card-body pb-2">
                        <p href="#" class="title text-bold">{{ product.name }}</p>
                        </div>
                        <div class="card-footer pt-2">
                        <div class="d-flex flex-row justify-content-between align-items-center">
                            <span class="flex-grow-1">${{ product.price.toFixed(2) }}</span>
                            <button class="btn btn-sm btn-outline-primary" @click="addItemToCart(product.id)" :disabled="buttonIsLoading">
                                <span class="spinner-border" v-if="buttonIsLoading && (addedItem == product.id)" role="status" style="width: 12px; height:12px">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                                <span>Add to cart</span>
                            </button>
                        </div>
                        </div>
                    </div>
                </div>

            </div>

            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item">
                        <button class="page-link btn btn-primary-outline" aria-label="Previous" @click="goToPrevPage()">
                            <i class="fa fa-chevron-left"></i>
                        </button>
                    </li>
                    <li class="page-item">
                        <input type="number" min="1" :max="lastPage" v-model="currentPage" class="form-control">
                    </li>
                    <li class="page-item">
                        <button class="page-link btn btn-primary-outline" aria-label="Next" @click="goToNextPage()">
                            <i class="fa fa-chevron-right"></i>
                        </button>
                    </li>
                </ul>
            </nav>

        </div>
    </div>
    <div class="row g-4 mb-4 mt-4" v-show="activeTab == 'cart' && !pageIsLoading" v-cloak>
        <div class="row justify-content-center">
            <div class="cus-xl-9 col-8">
                <div class="product-cart mb-sm-0 mb-20">
                    <div class="table-responsive">
                        <table id="cart" class="table table-bordered table-hover">
                            <thead>
                                <tr class="product-cart__header">
                                    <th scope="col">Product</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col" class="text-center">total</th>
                                    <th scope="col" class=""></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(orderItem, index) in cart">
                                    <td class="product-cart-title">
                                        <div class="media">
                                            <img class="product-cart-img" :src="orderItem.product.image_url" />
                                            <div class="media-body">
                                                <h5 class="mt-0">{{orderItem.product.name}}</h5>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price">${{orderItem.product.price}}</td>
                                    <td>{{ orderItem.quantity }}</td>
                                    <td class="text-center subtotal">${{ orderItem.product.price * orderItem.quantity }}</td>
                                    <td class="actions">
                                        <button type="button" class="btn btn-danger float-end" @click="deletItemFromOrder(orderItem.product.id)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header border-bottom-0 p-2">
                        <h5>Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="total mb-4">
                            Total:
                            <span>${{ order.total }}</span>
                        </div>
                        <div>
                            <div id="subscription-form" class="m-0" method="POST">
                                <div class="form mb-4">
                                    <input type="email" class="form-control" v-model="custumerEmail" aria-label="Email" placeholder="Email">
                                </div>
                                <div id="card-element" class="MyCardElement mb-4">
                                    <!-- Elements will create input elements here -->
                                </div>

                                <!-- We'll put the error messages in this element -->
                                <div id="card-errors" role="alert" class="alert mb-2 text-danger"></div>
                                <button type="button" class="btn btn-primary rounded-pill m-0 col-md-6 col-lg-6 col-xl-6" @click="createPaymentMethod">Pay</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-if="pageIsLoading" class="row. page-loader">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</section>
<input type="text" value="<?php echo $_ENV['STRIPE_PUB_KEY'] ?>" id="stripe_key" style="display:none">