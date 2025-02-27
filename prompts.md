**#Notifications adjustment**
When we are clearing all notifications, they are still appearing in the notifications modal after reloading the page. Once cleared we must remove all notifications related to the user.

**#Image upload adjustment**
On the bulk page, we are currently adding images in bulk, but this system does not allow me to allocate images to each product, we need to review the whole system to make the upload of images per row and not in bulk. While doing it per row for all rows at the same time it will be also considered to be updated in bulk.
When we are assigning an image to a product, instead of using the exact image it keeps on duplicating it in our products/ folder. We need to make sure that when we are using an image from our folder it will not duplicate more images inside the folder and instead use the exact same image that was allocated to product.

**#Delete Product Icon Button**
Here its simple, i want to vertically align the bin icon with the delete text at the bottom of the products card.

**#Specify the Delivery rate**
I want you to add to my Admin dashboard a new card that will act as a setting to set the delivery rate that will be rendered on the Order summary of the customer.

**#orders.php Order Management page**
On the Order Management page, i want to add a column that would retrieve and display the phone number related to a customer

**#Check out page**
I want to allow the user to set a delivery date when placing order that will be then viewed on the received order in the Order Management page on the Admin dashboard. 
I also want to add the delivery date to the message template sent on whatsapp, along with the user full name and phone number.

**#Register Page**
On the register page, i want to add one more field, phone number that will be required and saved in our database to be displayed on the user profile page too.
Also when a user is signing up with his Google account, we must redirect the user to the index page in our main directory, its currently being stuck on the same register page while being signed in.

**#Sign in button**
Instead of placing a sign in button the way we are when a user is not signed in, instead add as a lable near the profile icon when a user is not signed in, since either way the profile icon in that scenario is redirecting the user to the signing in/up page. (We can visually keep the icon colors as they are no need to give them the style of the sign in button)

**#Hero**
I want to replace the Hero of my index page, to replace Welcome to Flower Lab, Beautiful flowers for every occasion  and the shop now button with a big dynamic and modern slider, where we will place up to 4 slides of images that would slide smoothly automatically

**#Basket Icon**
The Basket icon gray clickable background is not a perfect circle, fix it

**#Categories**
We are currently retrieving categories that are pre-inserted in our database and then retrieving them on the Add New product page, selecting a category would set the product in different sections based on their category as it is currently the case, we will not change that. What i want is for the admin to be able to manually generate new categories and then be able to select them and assign these categories to the products he is creating.

**#Search Bar**
I want a search bar near the links in our header navbar, when looking for a product, we would display all the product cards that have either a common name or common category value (or part of it for both) 

**#htaccess**
Create a .htaccess file to place in the directory that would remove all the .php extensions to our pages
Prevent outsiders to manually insert files names and access our fragile files without affecting our functionalities. Create the related error 404 is it? page that respects the layout of our project and that displays to the user that he is not allowed to access the page.

**#Product cards stretching**
Currently our product cards are stretching too much on smaller screens. We need to ensure that we are not stretching our product images making them ugly to look at. The presentation must always be intact, modern, minimalist and userfriendly.

**#Removing item from basket**
We need to use our same modern notification feature when removing a product from the basket.